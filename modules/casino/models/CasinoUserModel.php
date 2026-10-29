<?php

class CasinoUserModel
{
    use BasicModel;

    public static $sessionKey = 'casino_user';
    /** Set when the player logged out of the casino: then the back-office login is not used automatically. */
    public static $noBackOfficeKey = 'casino_no_backoffice';

    public static function __constructStatic()
    {
        self::$table = "casino_user";
        self::$primaryKey = "casino_user_id";
        self::$sqlOrderBy = ["casino_user_id" => 'desc'];
        self::$selectOrderBy = [
            'casino_user_id#desc' => 'ID (aflopend)',
            'balance#desc' => 'Fiches (aflopend)',
            'balance#asc' => 'Fiches (oplopend)',
            'last_seen_at#desc' => 'Laatst gezien (aflopend)',
            'name#asc' => 'Naam (oplopend)',
        ];
    }

    private static function tableName(): string
    {
        return CIDb()->dbprefix(self::$table);
    }

    /** Id of the logged in player, 0 when nobody is logged in. Own session key, never mixed up with the back office login. */
    public static function userId(): int
    {
        $session = CISession()->userdata(self::$sessionKey);
        return isset($session[self::$primaryKey]) ? (int)$session[self::$primaryKey] : 0;
    }

    /** The logged in player fresh from the database; logs out a player that was blocked meanwhile. */
    public static function current(): array
    {
        $id = self::userId();
        if ($id <= 0 && !CISession()->userdata(self::$noBackOfficeKey)) {
            $id = self::loginFromBackOffice(); // a webmaster logged in to the back office does not need Google
        }
        if ($id <= 0) {
            return [];
        }
        $rs = self::find($id);
        if (empty($rs) || empty($rs['is_active'])) {
            self::logout(false);
            return [];
        }
        $session = CISession()->userdata(self::$sessionKey);
        if ((int)($session['seen'] ?? 0) < time() - 30) {
            CIDb()->query('UPDATE ' . self::tableName() . ' SET last_seen_at = NOW() WHERE casino_user_id = ?', [$id]);
            $session['seen'] = time();
            CISession()->set_userdata(self::$sessionKey, $session);
        }
        return $rs;
    }

    public static function find(int $id): array
    {
        $rs = CIDb()->query('SELECT * FROM ' . self::tableName() . ' WHERE casino_user_id = ?', [$id])->row_array();
        return is_array($rs) ? $rs : [];
    }

    public static function balance(int $id): int
    {
        $rs = CIDb()->query('SELECT balance FROM ' . self::tableName() . ' WHERE casino_user_id = ?', [$id])->row_array();
        return (int)($rs['balance'] ?? 0);
    }

    /** Takes chips for a bet; throws when the player has not enough. Returns the new balance. */
    public static function debit(int $id, int $amount): int
    {
        if ($amount <= 0) {
            throw new CasinoException(CasinoLang::t('err_invalid_bet'));
        }
        CIDb()->query(
            'UPDATE ' . self::tableName() . ' SET balance = balance - ?, total_bet = total_bet + ? WHERE casino_user_id = ? AND is_active = 1 AND balance >= ?',
            [$amount, $amount, $id, $amount]
        );
        if (CIDb()->affected_rows() !== 1) {
            throw new CasinoException(CasinoLang::t('err_no_chips'));
        }
        return self::balance($id);
    }

    /** Pays out chips (stake plus winnings). $win is the part that counts as winnings in the statistics. */
    public static function credit(int $id, int $amount, int $win = 0): int
    {
        if ($amount > 0) {
            CIDb()->query(
                'UPDATE ' . self::tableName() . ' SET balance = balance + ?, total_win = total_win + ? WHERE casino_user_id = ?',
                [$amount, max(0, $win), $id]
            );
        }
        return self::balance($id);
    }

    /** Gives a bet back as if it was never placed. */
    public static function refund(int $id, int $amount): int
    {
        if ($amount > 0) {
            CIDb()->query(
                'UPDATE ' . self::tableName() . ' SET balance = balance + ?, total_bet = GREATEST(0, CAST(total_bet AS SIGNED) - ?) WHERE casino_user_id = ?',
                [$amount, $amount, $id]
            );
        }
        return self::balance($id);
    }

    /** Moves chips from the balance to a poker table (buy-in); no bet, so the statistics do not change. Returns the new balance. */
    public static function withdraw(int $id, int $amount): int
    {
        if ($amount <= 0) {
            throw new CasinoException(CasinoLang::t('err_invalid_bet'));
        }
        CIDb()->query(
            'UPDATE ' . self::tableName() . ' SET balance = balance - ? WHERE casino_user_id = ? AND is_active = 1 AND balance >= ?',
            [$amount, $id, $amount]
        );
        if (CIDb()->affected_rows() !== 1) {
            throw new CasinoException(CasinoLang::t('err_no_chips'));
        }
        return self::balance($id);
    }

    /** Moves chips from a poker table back to the balance. Returns the new balance. */
    public static function deposit(int $id, int $amount): int
    {
        if ($amount > 0) {
            CIDb()->query('UPDATE ' . self::tableName() . ' SET balance = balance + ? WHERE casino_user_id = ?', [$amount, $id]);
        }
        return self::balance($id);
    }

    /** Statistics of games that are not settled through debit/credit (poker): what was bet and what was won on top. */
    public static function addStats(int $id, int $bet, int $win): void
    {
        if ($bet > 0 || $win > 0) {
            CIDb()->query(
                'UPDATE ' . self::tableName() . ' SET total_bet = total_bet + ?, total_win = total_win + ? WHERE casino_user_id = ?',
                [max(0, $bet), max(0, $win), $id]
            );
        }
    }

    /** Admin change of the balance (can be negative); the balance never drops below 0. Returns the real change. */
    public static function adjust(int $id, int $delta): int
    {
        $before = self::balance($id);
        CIDb()->query('UPDATE ' . self::tableName() . ' SET balance = GREATEST(0, balance + ?) WHERE casino_user_id = ?', [$delta, $id]);
        return self::balance($id) - $before;
    }

    /** Seconds until the next daily bonus, 0 when it can be claimed now, -1 when the bonus is off. */
    public static function bonusWait(array $user): int
    {
        if (CasinoConfigModel::int('casino_daily_bonus') <= 0) {
            return -1;
        }
        if (empty($user['bonus_at'])) {
            return 0;
        }
        return max(0, strtotime($user['bonus_at']) + 86400 - time());
    }

    public static function claimBonus(int $id): int
    {
        $amount = CasinoConfigModel::int('casino_daily_bonus');
        if ($amount <= 0) {
            throw new CasinoException(CasinoLang::t('bonus_off'));
        }
        CIDb()->query(
            'UPDATE ' . self::tableName() . ' SET balance = balance + ?, bonus_at = NOW() WHERE casino_user_id = ? AND is_active = 1 AND (bonus_at IS NULL OR bonus_at <= NOW() - INTERVAL 1 DAY)',
            [$amount, $id]
        );
        if (CIDb()->affected_rows() !== 1) {
            throw new CasinoException(CasinoLang::t('bonus_taken'));
        }
        CasinoLogModel::write($id, 'bonus', $amount);
        return $amount;
    }

    public static function clientId(): string
    {
        return CasinoConfigModel::get('casino_google_client_id');
    }

    /**
     * Checks the Google ID token (the "credential" of Google Identity Services) with Google
     * and returns its claims, or an empty array when the token is not valid for this site.
     */
    public static function verifyGoogleToken(string $idToken = ''): array
    {
        $clientId = self::clientId();
        if ($idToken === '' || $clientId === '') {
            return [];
        }

        $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status !== 200) {
            return [];
        }

        $claims = json_decode($body, true);
        if (!is_array($claims)) {
            return [];
        }

        $issuerOk = in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
        $audienceOk = ($claims['aud'] ?? '') === $clientId;
        $notExpired = (int)($claims['exp'] ?? 0) > time();
        $emailOk = ($claims['email_verified'] ?? '') === 'true' || ($claims['email_verified'] ?? false) === true;
        if (!$issuerOk || !$audienceOk || !$notExpired || !$emailOk || empty($claims['sub']) || empty($claims['email'])) {
            return [];
        }
        return $claims;
    }

    /**
     * Logs in with a verified Google token and creates the player (with the start chips) on the first login.
     * Returns the player id, 0 when the token is not valid, -1 when the player is blocked.
     */
    public static function loginWithGoogle(string $idToken = ''): int
    {
        $claims = self::verifyGoogleToken($idToken);
        if (empty($claims)) {
            return 0;
        }

        $data = [
            'email' => mb_substr($claims['email'], 0, 255),
            'name' => mb_substr($claims['name'] ?? $claims['email'], 0, 100),
            'picture' => mb_substr($claims['picture'] ?? '', 0, 500),
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];

        $exist = CIDb()->query('SELECT * FROM ' . self::tableName() . ' WHERE google_sub = ?', [$claims['sub']])->row_array();
        if (empty($exist)) {
            $data['google_sub'] = $claims['sub'];
            $data['balance'] = CasinoConfigModel::int('casino_start_balance');
            CIDb()->insert(self::$table, $data);
            $id = (int)CIDb()->insert_id();
            if ($id > 0) {
                CasinoLogModel::write($id, 'register', (int)$data['balance'], $data['email']);
            }
        } else {
            $id = (int)$exist[self::$primaryKey];
            if (empty($exist['is_active'])) {
                CasinoLogModel::write($id, 'login', 0, 'Geblokkeerd, inloggen geweigerd');
                return -1;
            }
            CIDb()->where(self::$primaryKey, $id)->update(self::$table, $data);
        }

        if ($id <= 0) {
            return 0;
        }

        CISession()->sess_regenerate();
        CISession()->set_userdata(self::$sessionKey, [self::$primaryKey => $id, 'seen' => time()]);
        CasinoLogModel::write($id, 'login', 0, $data['email']);
        return $id;
    }

    public static function logout(bool $log = true)
    {
        $id = self::userId();
        if ($log && $id > 0) {
            CasinoLogModel::write($id, 'logout');
            // logged out on purpose: no automatic login with the back office until the player asks for it
            CISession()->set_userdata(self::$noBackOfficeKey, 1);
        }
        CISession()->unset_userdata(self::$sessionKey);
    }

    /** Back-office user id when a webmaster (super user) is logged in to the back office, else 0. */
    public static function backOfficeWebmaster(): array
    {
        $backId = LoginModel::userId();
        if ($backId <= 0) {
            return [];
        }
        $back = CIDb()->query('SELECT * FROM ' . CIDb()->dbprefix('user') . ' WHERE user_id = ? AND is_active = 1 AND is_del = 0', [$backId])->row_array();
        return !empty($back) && UserModel::isSuperUser($back) ? $back : [];
    }

    /**
     * Logs in the webmaster of the back office as a casino player (no Google needed). The player is linked to the
     * back-office user (google_sub "backoffice:<user id>") and is created with the start chips on the first visit.
     * Returns the player id, 0 when no webmaster is logged in, -1 when that player is blocked.
     */
    public static function loginFromBackOffice(): int
    {
        $back = self::backOfficeWebmaster();
        if (empty($back)) {
            return 0;
        }
        CISession()->unset_userdata(self::$noBackOfficeKey);
        $sub = 'backoffice:' . (int)$back['user_id'];
        $email = (string)($back['emailaddress'] ?? '');
        $data = [
            'email' => mb_substr($email, 0, 255),
            'name' => mb_substr(trim((string)($back['display_info'] ?? '')) ?: 'Webmaster', 0, 100),
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];
        $exist = CIDb()->query('SELECT * FROM ' . self::tableName() . ' WHERE google_sub = ?', [$sub])->row_array();
        if (empty($exist)) {
            $data['google_sub'] = $sub;
            $data['picture'] = '';
            $data['balance'] = CasinoConfigModel::int('casino_start_balance');
            CIDb()->insert(self::$table, $data);
            $id = (int)CIDb()->insert_id();
            if ($id <= 0) {
                return 0;
            }
            CasinoLogModel::write($id, 'register', (int)$data['balance'], 'Back office: ' . $email);
        } else {
            $id = (int)$exist[self::$primaryKey];
            if (empty($exist['is_active'])) {
                return -1;
            }
            CIDb()->where(self::$primaryKey, $id)->update(self::$table, $data);
        }
        CISession()->set_userdata(self::$sessionKey, [self::$primaryKey => $id, 'seen' => time()]);
        CasinoLogModel::write($id, 'login', 0, 'Back office (webmaster): ' . $email);
        return $id;
    }

    /** Display name for the other players at a table: first name only, never the email address. */
    public static function publicName(array $user): string
    {
        $name = trim((string)($user['name'] ?? ''));
        if ($name === '' || str_contains($name, '@')) {
            $name = CasinoLang::t('player_fallback', ['id' => (int)($user[self::$primaryKey] ?? 0)]);
        }
        return mb_substr(explode(' ', $name)[0], 0, 20);
    }
}
