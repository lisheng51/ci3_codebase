<?php
// Casino texts. Placeholders like {amount} are filled in by CasinoLang::t() / Casino.t().
// Keys with casino_js_ are also sent to the game scripts.
$lang["casino_title"] = "娱乐场";
$lang["casino_login_title"] = "娱乐场 - 登录";
$lang["casino_login_intro"] = "和其他玩家一起玩骰宝和 21 点。使用虚拟筹码，仅供娱乐。";
$lang["casino_login_not_configured"] = "还没有设置登录（后台 > Casino > 设置 > Google Client ID）。";
$lang["casino_login_failed"] = "Google 登录失败。";
$lang["casino_login_blocked"] = "你的账号已被封禁。";
$lang["casino_logged_out"] = "你已退出登录。";
$lang["casino_logout"] = "退出";
$lang["casino_chips"] = "筹码";
$lang["casino_language"] = "语言";
$lang["casino_choose_table"] = "选择一张桌子";
$lang["casino_no_tables"] = "目前还没有开放的桌子。";
$lang["casino_bonus_wait"] = "{hours} 小时后可领奖励";
$lang["casino_bonus_claim"] = "领取 {amount} 奖励筹码";
$lang["casino_bonus_received"] = "已领取 {amount} 筹码！";
$lang["casino_bonus_off"] = "每日奖励已关闭。";
$lang["casino_bonus_taken"] = "今天已经领过奖励了。";
$lang["casino_history"] = "记录";
$lang["casino_my_history"] = "我的记录";
$lang["casino_online"] = "{count} 人在线";
$lang["casino_lobby"] = "大厅";
$lang["casino_loading"] = "加载中…";
$lang["casino_col_time"] = "时间";
$lang["casino_col_game"] = "游戏";
$lang["casino_col_action"] = "操作";
$lang["casino_col_chips"] = "筹码";
$lang["casino_col_balance"] = "余额";
$lang["casino_col_details"] = "详情";
$lang["casino_player_fallback"] = "玩家 {id}";

$lang["casino_game_dice"] = "骰宝 (Sic Bo)";
$lang["casino_game_blackjack"] = "21 点 (Blackjack)";

$lang["casino_action_register"] = "新玩家";
$lang["casino_action_login"] = "登录";
$lang["casino_action_logout"] = "退出";
$lang["casino_action_bonus"] = "每日奖励";
$lang["casino_action_admin"] = "管理员调整";
$lang["casino_action_join"] = "入座";
$lang["casino_action_leave"] = "离桌";
$lang["casino_action_bet"] = "下注";
$lang["casino_action_refund"] = "退回下注";
$lang["casino_action_double"] = "加倍";
$lang["casino_action_hit"] = "要牌";
$lang["casino_action_stand"] = "停牌";
$lang["casino_action_timeout"] = "超时";
$lang["casino_action_roll"] = "开骰";
$lang["casino_action_deal"] = "发牌";
$lang["casino_action_win"] = "赢";
$lang["casino_action_lose"] = "输";
$lang["casino_action_push"] = "和局";
$lang["casino_action_blackjack"] = "黑杰克";

$lang["casino_err_invalid_bet"] = "下注无效。";
$lang["casino_err_no_chips"] = "筹码不足。";
$lang["casino_err_busy"] = "桌子正忙，请再试一次。";
$lang["casino_err_table_not_found"] = "找不到这张桌子。";
$lang["casino_err_table_closed"] = "这张桌子已关闭。";
$lang["casino_err_wait_round"] = "请等下一局。";
$lang["casino_err_unknown_spot"] = "未知的下注位置。";
$lang["casino_err_min_bet"] = "最低下注 {amount}。";
$lang["casino_err_max_spot"] = "每个位置最多 {amount}。";
$lang["casino_err_max_bet"] = "最高下注 {amount}。";
$lang["casino_err_nothing_to_clear"] = "没有可以撤回的下注。";
$lang["casino_err_unknown_action"] = "未知操作。";
$lang["casino_err_table_full"] = "桌子已满。";
$lang["casino_err_sit_first"] = "请先入座。";
$lang["casino_err_not_your_turn"] = "还没轮到你。";
$lang["casino_err_double_first_two"] = "只有前两张牌时才能加倍。";

$lang["casino_dice_small"] = "小";
$lang["casino_dice_big"] = "大";
$lang["casino_dice_odd"] = "单";
$lang["casino_dice_even"] = "双";
$lang["casino_dice_triple"] = "全围";
$lang["casino_dice_triple_hint"] = "三颗相同";
$lang["casino_dice_per_die"] = "每颗 1:1";
$lang["casino_dice_triple_rule"] = "开出围骰（三颗相同）时，大、小、单、双都算输。";
$lang["casino_clear_bet"] = "撤回下注";
$lang["casino_players_at_table"] = "桌上玩家";
$lang["casino_last_rolls"] = "最近结果";

$lang["casino_bj_dealer"] = "庄家";
$lang["casino_bj_bet"] = "下注";
$lang["casino_bj_back"] = "撤回";
$lang["casino_bj_hit"] = "要牌";
$lang["casino_bj_stand"] = "停牌";
$lang["casino_bj_double"] = "加倍";
$lang["casino_bj_sit_hint"] = "点击空位入座。";

$lang["casino_js_error"] = "出错了。";
$lang["casino_js_no_connection"] = "无法连接服务器。";
$lang["casino_js_you"] = "你";
$lang["casino_js_round_n"] = "第 {n} 局";
$lang["casino_js_place_bet_start"] = "下注后本局开始倒计时";
$lang["casino_js_place_bet"] = "请下注…";
$lang["casino_js_rolling"] = "摇骰中…";
$lang["casino_js_total"] = "{total} 点";
$lang["casino_js_you_win"] = "你赢了 {amount}！";
$lang["casino_js_you_lose"] = "你输了 {amount}";
$lang["casino_js_push"] = "不输不赢";
$lang["casino_js_won_toast"] = "赢了：+{amount}";
$lang["casino_js_nobody"] = "还没有人";
$lang["casino_js_no_rolls"] = "还没有结果";
$lang["casino_js_big"] = "大";
$lang["casino_js_small"] = "小";
$lang["casino_js_triple"] = "围";
$lang["casino_js_rules"] = "下注 {min} - {max} · 黑杰克赔 {payout}:1 · {soft17}";
$lang["casino_js_hits_soft17"] = "庄家软 17 要牌";
$lang["casino_js_stands_soft17"] = "庄家软 17 停牌";
$lang["casino_js_take_seat"] = "入座";
$lang["casino_js_free"] = "空位";
$lang["casino_js_win"] = "赢";
$lang["casino_js_lose"] = "输";
$lang["casino_js_blackjack"] = "黑杰克！";
$lang["casino_js_bust"] = "爆牌";
$lang["casino_js_waiting_bets"] = "等待下注";
$lang["casino_js_betting_soon"] = "下注中…马上发牌";
$lang["casino_js_your_bet_wait"] = "你的下注：{amount} · 等待其他玩家";
$lang["casino_js_your_turn"] = "轮到你了！";
$lang["casino_js_turn_of"] = "轮到 {name}";
$lang["casino_js_dealer_result"] = "庄家：{value}";
$lang["casino_js_dealer_bust"] = "庄家：{value}（爆牌）";
$lang["casino_js_online"] = "{count} 人在线";
$lang["casino_js_shaker_title"] = "骰宝";
$lang["casino_js_tap_skip"] = "点击跳过";
$lang["casino_js_tap_continue"] = "点击继续";

// roulette
$lang["casino_game_roulette"] = "轮盘 (Roulette)";
$lang["casino_action_spin"] = "转盘";
$lang["casino_rl_low"] = "1 - 18";
$lang["casino_rl_high"] = "19 - 36";
$lang["casino_rl_dozen_1"] = "第一打";
$lang["casino_rl_dozen_2"] = "第二打";
$lang["casino_rl_dozen_3"] = "第三打";
$lang["casino_rl_zero_rule"] = "开出 0 时所有外围注都输。单个号码赔 35:1，打和列赔 2:1，其余 1:1。";
$lang["casino_last_numbers"] = "最近号码";
$lang["casino_js_red"] = "红";
$lang["casino_js_black"] = "黑";
$lang["casino_js_green"] = "绿";
$lang["casino_js_odd"] = "单";
$lang["casino_js_even"] = "双";
$lang["casino_js_spinning"] = "轮盘转动中…";
$lang["casino_js_no_more_bets"] = "停止下注";
$lang["casino_js_wheel_title"] = "轮盘";

// confirm before leaving
$lang["casino_js_confirm_logout"] = "确定要退出登录吗？";
$lang["casino_js_confirm_leave"] = "你在这张桌上还有下注或座位，确定要离开吗？";
$lang["casino_js_confirm_leave_hint"] = "未开奖的下注会退回；正在进行的牌局会自动停牌。";
$lang["casino_js_yes"] = "确定";
$lang["casino_js_cancel"] = "取消";

// dice board sections
$lang["casino_dice_sec_main"] = "大小单双";
$lang["casino_dice_sec_doubles"] = "对子";
$lang["casino_dice_sec_triples"] = "围骰";
$lang["casino_dice_sec_totals"] = "点数";
$lang["casino_dice_sec_combos"] = "两骰组合";
$lang["casino_dice_sec_singles"] = "三军";
$lang["casino_dice_singles_rule"] = "押一个号码：出现 1 颗赔 1:1，2 颗赔 {two}:1，3 颗赔 {three}:1。";

// win / loss record at the table
$lang["casino_my_records"] = "我的输赢记录";
$lang["casino_rec_round"] = "局";
$lang["casino_rec_result"] = "开奖";
$lang["casino_rec_bet"] = "下注";
$lang["casino_rec_net"] = "输赢";
$lang["casino_js_rec_total"] = "最近 {n} 局合计：";
$lang["casino_js_rec_none"] = "你还没在这张桌上玩过。";

// animation and sound buttons
$lang["casino_anim_toggle"] = "动画 开/关";
$lang["casino_sound_toggle"] = "声音 开/关";

// fruit machine
$lang["casino_game_slot"] = "水果机";
$lang["casino_action_jackpot"] = "累积大奖";
$lang["casino_sl_jackpot"] = "累积大奖 JACKPOT";
$lang["casino_sl_line_bet"] = "每条线下注";
$lang["casino_sl_total_bet"] = "总下注";
$lang["casino_sl_spin"] = "开转";
$lang["casino_sl_paytable"] = "赔付表";
$lang["casino_sl_cherry2"] = "左起 2 个樱桃";
$lang["casino_sl_cherry1"] = "最左 1 个樱桃";
$lang["casino_sl_rule"] = "可选 1 到 5 条中奖线（第 1 线是中间那条），每条线赔「倍数 × 每线下注」。中间线出现 777 还能赢得累积大奖：每线下注达到最高时拿走整个奖池，否则按比例获得。";
$lang["casino_sl_recent"] = "这台机器最近的中奖";
$lang["casino_js_sl_spinning"] = "转动中…";
$lang["casino_js_sl_press_spin"] = "选好下注，按「开转」（或空格键）";
$lang["casino_js_sl_no_win"] = "没中，再来一次";
$lang["casino_js_sl_line"] = "第 {n} 线";
$lang["casino_js_sl_win"] = "赢了 {amount}";
$lang["casino_js_sl_big_win"] = "大奖 +{amount}";
$lang["casino_js_sl_jackpot_win"] = "累积大奖！+{amount}";

// Texas Hold'em
$lang["casino_game_holdem"] = '德州扑克';
$lang["casino_pk_rules"] = '无限注德州扑克 · 盲注 {sb}/{bb} · 带入 {min} - {max}';
$lang["casino_action_buyin"] = '带入筹码';
$lang["casino_action_cashout"] = '带出筹码';
$lang["casino_action_blind"] = '盲注';
$lang["casino_action_fold"] = '弃牌';
$lang["casino_action_check"] = '过牌';
$lang["casino_action_call"] = '跟注';
$lang["casino_action_raise"] = '加注';
$lang["casino_action_allin"] = '全下';
$lang["casino_action_rake"] = '抽水';
$lang["casino_err_pk_buyin"] = '带入金额必须在 {min} 到 {max} 之间。';
$lang["casino_err_pk_cannot_check"] = '有人下注了，不能过牌。';
$lang["casino_err_pk_min_raise"] = '至少加注到 {amount}。';
$lang["casino_err_pk_cannot_raise"] = '现在不能再加注。';
$lang["casino_js_pk_fold"] = '弃牌';
$lang["casino_js_pk_check"] = '过牌';
$lang["casino_js_pk_call"] = '跟注 {amount}';
$lang["casino_js_pk_bet"] = '下注 {amount}';
$lang["casino_js_pk_raise"] = '加注到 {amount}';
$lang["casino_js_pk_allin"] = '全下';
$lang["casino_js_pk_min"] = '最小';
$lang["casino_js_pk_half_pot"] = '半池';
$lang["casino_js_pk_pot"] = '满池';
$lang["casino_js_pk_pot_total"] = '底池：{amount}';
$lang["casino_js_pk_last_call"] = '跟注';
$lang["casino_js_pk_last_raise"] = '加注';
$lang["casino_js_pk_buyin_title"] = '带多少筹码上桌？';
$lang["casino_js_pk_buyin_hint"] = '{min} 到 {max} 之间。离座时剩下的筹码会退回你的余额。';
$lang["casino_js_pk_sit"] = '入座';
$lang["casino_js_pk_sit_hint"] = '点击空座位入座';
$lang["casino_js_pk_waiting"] = '等待玩家（至少 2 人）';
$lang["casino_js_pk_next_hand"] = '下一局马上开始…';
$lang["casino_js_pk_sitting_out"] = '下一局开始加入';
$lang["casino_js_pk_leaving"] = '本局后离座';
$lang["casino_js_pk_wins"] = '{name} 赢得 {amount}';
$lang["casino_js_pk_with"] = '（{hand}）';
$lang["casino_js_pk_your_hand"] = '你的牌：{hand}';
$lang["casino_js_pk_blinds"] = '盲注 {sb}/{bb}';
$lang["casino_js_hand_high_card"] = '高牌';
$lang["casino_js_hand_pair"] = '一对';
$lang["casino_js_hand_two_pair"] = '两对';
$lang["casino_js_hand_trips"] = '三条';
$lang["casino_js_hand_straight"] = '顺子';
$lang["casino_js_hand_flush"] = '同花';
$lang["casino_js_hand_full_house"] = '葫芦';
$lang["casino_js_hand_quads"] = '四条';
$lang["casino_js_hand_straight_flush"] = '同花顺';
$lang["casino_js_hand_royal_flush"] = '皇家同花顺';

// bingo
$lang["casino_game_bingo"] = '宾果';
$lang["casino_action_line"] = '一条线';
$lang["casino_action_bingo"] = '宾果';
$lang["casino_err_bg_max_cards"] = '每局最多 {max} 张卡。';
$lang["casino_bg_pot"] = '奖池';
$lang["casino_bg_line_prize"] = '第一条线';
$lang["casino_bg_full_prize"] = '满卡（宾果）';
$lang["casino_bg_my_cards"] = '我的卡';
$lang["casino_bg_board"] = '已开号码';
$lang["casino_bg_cancel"] = '退卡';
$lang["casino_bg_history"] = '往局记录';
$lang["casino_bg_rules"] = '每张卡 {price}。号码自动标记。第一个连成一条线（横、竖或斜）的赢奖池的 {line}%，第一张满卡赢剩下的。奖池为卖卡总额的 {payout}%；同一个球同时中奖的平分。';
$lang["casino_js_bg_buy_n"] = '买 {n} 张（{amount}）';
$lang["casino_js_bg_buy_start"] = '买卡后本局开始倒计时';
$lang["casino_js_bg_buying"] = '买卡中…马上开始开球';
$lang["casino_js_bg_drawing"] = '开球中：第 {n} 个球';
$lang["casino_js_bg_line_won"] = '一条线！+{amount}';
$lang["casino_js_bg_bingo_won"] = '宾果！+{amount}';
$lang["casino_js_bg_line_by"] = '一条线：{names}';
$lang["casino_js_bg_full_by"] = '宾果：{names}';
$lang["casino_js_bg_line_badge"] = '一条线';
$lang["casino_js_bg_to_go"] = '还差 {n}';
$lang["casino_js_bg_no_cards"] = '本局你没有卡。';
$lang["casino_js_bg_cards_n"] = '{n} 张卡';
$lang["casino_js_bg_round_line"] = '第 {round} 局：{balls} 个球';

// what a bet can win
$lang["casino_js_can_win"] = '可赢 +{amount}';
$lang["casino_js_can_win_max"] = '最多赢 +{amount}';
$lang["casino_js_sl_jackpot_short"] = '奖池';

// solo tables
$lang["casino_solo"] = '单人';
$lang["casino_err_solo_no_bets"] = '请先下注。';
$lang["casino_js_solo_go_dice"] = '摇骰子';
$lang["casino_js_solo_go_roulette"] = '转轮盘';
$lang["casino_js_solo_hint"] = '下注后自己开始，不用等别人';
$lang["casino_js_solo_ready"] = '准备好了就按按钮（或空格键）';

// solo blackjack
$lang["casino_js_solo_go_bj"] = '发牌';

// fruit machine lines
$lang["casino_sl_lines"] = '线数';

// bingo record
$lang["casino_js_bg_balls"] = '{n} 个球';

// Sic Bo casino board
$lang["casino_dice_layout_casino"] = '赌场版';
$lang["casino_dice_layout_list"] = '分区版';
$lang["casino_dice_pays"] = '1 赔 {n}';
$lang["casino_dice_small_desc"] = '4 到 10 · 围骰输';
$lang["casino_dice_big_desc"] = '11 到 17 · 围骰输';
$lang["casino_dice_double_n"] = '对子 {n}';
$lang["casino_dice_triple_n"] = '围骰 {n}';
$lang["casino_dice_undo_last"] = '撤回上一注';
$lang["casino_dice_double_bets"] = '加倍';
$lang["casino_dice_rebet"] = '重复上局';

// webmaster login
$lang["casino_login_backoffice"] = '以 webmaster 账号进入（{name}）';

// 
$lang["casino_js_win_congrats"] = '恭喜，你赢了 {amount}';

// 
$lang["casino_fullscreen"] = '全屏';

// 
$lang["casino_game_doudizhu"] = '斗地主';
$lang["casino_ddz_base"] = '底分';
$lang["casino_ddz_bid"] = '叫分';
$lang["casino_ddz_mult"] = '倍数';
$lang["casino_ddz_rules"] = '三人对局：一个地主对两个农民。叫 1 到 3 分，叫分最高的当地主，拿 3 张底牌。先出完牌的一方获胜。得分 = 底分 x 叫分，每个炸弹 x{bomb}，王炸 x{rocket}，春天 x2。买入 {min} - {max}。';
$lang["casino_ddz_bot_1"] = '电脑 A';
$lang["casino_ddz_bot_2"] = '电脑 B';
$lang["casino_err_ddz_bid"] = '叫分要比当前高（1 到 3 分），或者不叫。';
$lang["casino_err_ddz_must_lead"] = '该你先出，不能不出。';
$lang["casino_err_ddz_cards"] = '你没有这些牌。';
$lang["casino_err_ddz_invalid"] = '这些牌不是有效的牌型。';
$lang["casino_err_ddz_lower"] = '要出同样牌型且更大的牌（或者炸弹）。';
$lang["casino_err_ddz_pick"] = '请先选择要出的牌。';
$lang["casino_err_ddz_stack"] = '桌上的筹码不够底分。';
$lang["casino_action_bid"] = '叫分';
$lang["casino_action_landlord"] = '当地主';
$lang["casino_action_play"] = '出牌';
$lang["casino_action_pass"] = '不出';
$lang["casino_js_ddz_pass"] = '不出';
$lang["casino_js_ddz_bid_n"] = '{n} 分';
$lang["casino_js_ddz_no_bid"] = '不叫';
$lang["casino_js_ddz_landlord"] = '地主';
$lang["casino_js_ddz_farmer"] = '农民';
$lang["casino_js_ddz_auto_on"] = '托管中';
$lang["casino_js_ddz_auto"] = '托管';
$lang["casino_js_ddz_auto_off"] = '取消托管';
$lang["casino_js_ddz_solo_start"] = '坐下，和两个电脑玩家对战。';
$lang["casino_js_ddz_solo_ready"] = '准备好了就点「发牌」。';
$lang["casino_js_ddz_wait_players"] = '等待三位玩家入座...';
$lang["casino_js_ddz_starting"] = '正在发牌...';
$lang["casino_js_ddz_your_bid"] = '轮到你叫分：要当地主吗？';
$lang["casino_js_ddz_bidding"] = '{name} 叫分中...';
$lang["casino_js_ddz_landlord_wins"] = '地主获胜';
$lang["casino_js_ddz_farmers_wins"] = '农民获胜';
$lang["casino_js_ddz_spring"] = '春天！';
$lang["casino_js_ddz_solo_sit"] = '入座';
$lang["casino_js_ddz_next_hand"] = '下一局';
$lang["casino_js_ddz_deal"] = '发牌';
$lang["casino_js_ddz_hint"] = '提示';
$lang["casino_js_ddz_play"] = '出牌';
$lang["casino_js_ddz_no_hint"] = '没有大过上家的牌，请选择不出。';

// 
$lang["casino_game_chess"] = '国际象棋';
$lang["casino_game_xiangqi"] = '中国象棋';
$lang["casino_err_board_move"] = '这步棋不合规则。';
$lang["casino_board_bot_easy"] = '电脑（简单）';
$lang["casino_board_bot_normal"] = '电脑（普通）';
$lang["casino_board_bot_hard"] = '电脑（困难）';
$lang["casino_board_rules_chess"] = '每局下注 {stake}，赢家拿走双方的注，和棋退回下注。被将死、认输、超时或离开都算输。和棋：逼和、50 回合无吃子无兵动、三次重复局面、子力不足或双方同意。';
$lang["casino_board_rules_xiangqi"] = '每局下注 {stake}，赢家拿走双方的注，和棋退回下注。无棋可走（被将死或困毙）算输，认输、超时或离开也算输。和棋：三次重复局面、60 回合无吃子，或双方都没有进攻棋子。';
$lang["casino_js_board_moves"] = '棋谱';
$lang["casino_js_board_moves_short"] = '回合';
$lang["casino_js_board_white"] = '白方';
$lang["casino_js_board_black"] = '黑方';
$lang["casino_js_board_red"] = '红方';
$lang["casino_js_board_away"] = '离线';
$lang["casino_js_board_waiting"] = '等待对手入座...';
$lang["casino_js_board_starting"] = '对局即将开始...';
$lang["casino_js_board_your_move"] = '轮到你走棋';
$lang["casino_js_board_check"] = '将军！';
$lang["casino_js_board_draw"] = '和棋';
$lang["casino_js_board_you_won"] = '你赢了！';
$lang["casino_js_board_you_lost"] = '你输了';
$lang["casino_js_board_wins"] = '{name} 获胜';
$lang["casino_js_board_reason_checkmate"] = '将死';
$lang["casino_js_board_reason_stalemate"] = '逼和';
$lang["casino_js_board_reason_stalemate_loss"] = '困毙';
$lang["casino_js_board_reason_fifty"] = '50 回合';
$lang["casino_js_board_reason_sixty"] = '60 回合无吃子';
$lang["casino_js_board_reason_repetition"] = '三次重复局面';
$lang["casino_js_board_reason_material"] = '子力不足';
$lang["casino_js_board_reason_resign"] = '认输';
$lang["casino_js_board_reason_time"] = '超时';
$lang["casino_js_board_reason_abandon"] = '中途离开';
$lang["casino_js_board_reason_agreed"] = '双方同意';
$lang["casino_js_board_resign"] = '认输';
$lang["casino_js_board_resign_confirm"] = '确定认输吗？你会输掉下注。';
$lang["casino_js_board_offer_draw"] = '提和';
$lang["casino_js_board_accept_draw"] = '同意和棋';
$lang["casino_js_board_draw_offered"] = '你已提和';
$lang["casino_js_board_draw_wanted"] = '对手提和';
$lang["casino_js_board_declined"] = '电脑拒绝和棋';
$lang["casino_js_board_solo_hint"] = '选择执棋颜色和难度，和电脑对弈。';
$lang["casino_js_board_play_as"] = '我执';
$lang["casino_js_board_level"] = '难度（赢得）';
$lang["casino_js_board_level_1"] = '简单';
$lang["casino_js_board_level_2"] = '普通';
$lang["casino_js_board_level_3"] = '困难';
$lang["casino_js_board_start"] = '开始对局（下注 {stake}）';
$lang["casino_js_board_promote"] = '兵升变为';
$lang["casino_js_ddz_bomb_fx"] = '炸弹！x{n}';
$lang["casino_js_ddz_rocket_fx"] = '王炸！x{n}';
