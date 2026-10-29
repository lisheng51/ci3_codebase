<?php

class Home extends Site_Controller
{
    protected $title_module = 'Casino';
    private $user = [];

    public function __construct()
    {
        parent::__construct();
        $this->user = CasinoUserModel::current();
        if (empty($this->user)) {
            redirect($this->module_path_name . '/auth/login');
        }
    }

    /** The lobby: all open tables with the number of players online. */
    public function index()
    {
        CasinoTableModel::sqlReset();
        CasinoTableModel::$sqlOrderBy = ["order_num" => 'asc', "casino_table_id" => 'asc'];
        CasinoTableModel::$sqlWhere = ['is_active' => 1];
        $listdb = [];
        foreach (CasinoTableModel::getAll() as $rs) {
            $rs['online'] = CasinoTableModel::onlineCount($rs);
            $rs['game_name'] = CasinoTableModel::gameName($rs['game']);
            $rs['play_url'] = site_url($this->controller_url . '/table/' . $rs['casino_table_id']);
            $listdb[] = $rs;
        }

        $data["listdb"] = $listdb;
        $data["user"] = $this->user;
        $data["bonusWait"] = CasinoUserModel::bonusWait($this->user);
        $data["bonusAmount"] = CasinoConfigModel::int('casino_daily_bonus');
        $data["bonusUrl"] = site_url($this->controller_url . '/bonus');
        $data["logoutUrl"] = site_url($this->module_path_name . '/auth/logout');
        $data["historyUrl"] = site_url($this->controller_url . '/history');
        $data["title"] = CasinoLang::t('title');
        $data["topbar"] = $this->topbar();
        $this->view_layout("index", $data);
    }

    private function topbar(): string
    {
        return $this->view_layout_return('_topbar', ['user' => $this->user]);
    }

    public function table(int $id = 0)
    {
        $table = CasinoTableModel::fetch($id);
        if (empty($table) || empty($table['is_active'])) {
            redirect($this->controller_url);
        }

        $data["table"] = $table;
        $data["user"] = $this->user;
        $data["config"] = [
            'game' => $table['game'],
            'stateUrl' => site_url($this->module_path_name . '/game/state/' . $id),
            'actUrl' => site_url($this->module_path_name . '/game/act/' . $id),
            'lobbyUrl' => site_url($this->controller_url),
            'pollMs' => CasinoConfigModel::int('casino_poll_ms', 500, 10000),
            'minBet' => (int)$table['min_bet'],
            'maxBet' => (int)$table['max_bet'],
            'locale' => CasinoLang::locale(),
            'text' => CasinoLang::js(),
        ];
        if ($table['game'] === 'slot') {
            $data["config"]['slot'] = CasinoSlotModel::clientConfig();
        }
        $data["title"] = $table['name'];
        $data["topbar"] = $this->topbar();
        // chess and xiangqi share the board page
        $views = ['blackjack' => 'blackjack', 'roulette' => 'roulette', 'slot' => 'slot', 'holdem' => 'holdem', 'bingo' => 'bingo',
            'doudizhu' => 'doudizhu', 'chess' => 'board', 'xiangqi' => 'board'];
        $this->view_layout($views[$table['game']] ?? 'dice', $data);
    }

    public function bonus()
    {
        try {
            $amount = CasinoUserModel::claimBonus((int)$this->user['casino_user_id']);
            $json = ['status' => 'good', 'msg' => CasinoLang::t('bonus_received', ['amount' => $amount]), 'balance' => CasinoUserModel::balance((int)$this->user['casino_user_id'])];
        } catch (CasinoException $e) {
            $json = ['status' => 'error', 'msg' => $e->getMessage()];
        }
        exit(json_encode($json));
    }

    /** The last moves of the player. */
    public function history()
    {
        CasinoLogModel::sqlReset();
        CasinoLogModel::$sqlOrderBy = ["casino_log_id" => 'desc'];
        CasinoLogModel::$sqlWhere = ['casino_user_id' => (int)$this->user['casino_user_id']];
        $data["listdb"] = CasinoLogModel::getList(100);
        $data["user"] = $this->user;
        $data["lobbyUrl"] = site_url($this->controller_url);
        $data["title"] = CasinoLang::t('my_history');
        $data["topbar"] = $this->topbar();
        $this->view_layout("history", $data);
    }
}
