<?php

/** Ajax endpoints of the game tables; both answer with the table as the player sees it. */
class Game extends Site_Controller
{
    private $user = [];

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        $this->user = CasinoUserModel::current();
        if (empty($this->user)) {
            exit(json_encode(['status' => 'login', 'msg' => CasinoLang::t('logged_out'), 'redirect' => site_url($this->module_path_name . '/auth/login')]));
        }
    }

    public function state(int $id = 0)
    {
        $this->answer(fn() => CasinoTableModel::poll($id, $this->user));
    }

    public function act(int $id = 0)
    {
        $action = (string)(CIInput()->post('action') ?? '');
        $post = [
            'spot' => (string)(CIInput()->post('spot') ?? ''),
            'amount' => (int)(CIInput()->post('amount') ?? 0),
            'seat' => (int)(CIInput()->post('seat') ?? 0),
            'lines' => (int)(CIInput()->post('lines') ?? 0),
            // dou dizhu: the bid, the cards played ("3S,3H") and auto play on / off
            'points' => (int)(CIInput()->post('points') ?? 0),
            'cards' => (string)(CIInput()->post('cards') ?? ''),
            'on' => (int)(CIInput()->post('on') ?? 0),
            // chess, xiangqi: the move ("e2e4") and, solo, the computer's level
            'move' => (string)(CIInput()->post('move') ?? ''),
            'level' => (int)(CIInput()->post('level') ?? 0),
        ];
        $this->answer(fn() => CasinoTableModel::act($id, $this->user, $action, $post));
    }

    private function answer(callable $fn)
    {
        try {
            $json = ['status' => 'good', 'state' => $fn()];
        } catch (CasinoException $e) {
            $json = ['status' => 'error', 'msg' => $e->getMessage()];
        }
        exit(json_encode($json));
    }
}
