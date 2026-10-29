<?php
// Casino texts. Placeholders like {amount} are filled in by CasinoLang::t() / Casino.t().
// Keys with casino_js_ are also sent to the game scripts.
$lang["casino_title"] = "Casino";
$lang["casino_login_title"] = "Casino - sign in";
$lang["casino_login_intro"] = "Dice and blackjack with other players. You play with virtual chips.";
$lang["casino_login_not_configured"] = "Sign-in is not set up yet (Admin > Casino > Settings > Google Client ID).";
$lang["casino_login_failed"] = "Signing in with Google failed.";
$lang["casino_login_blocked"] = "Your account has been blocked.";
$lang["casino_logged_out"] = "You have been signed out.";
$lang["casino_logout"] = "Sign out";
$lang["casino_chips"] = "Chips";
$lang["casino_language"] = "Language";
$lang["casino_choose_table"] = "Choose a table";
$lang["casino_no_tables"] = "No tables are open yet.";
$lang["casino_bonus_wait"] = "Bonus in {hours} h";
$lang["casino_bonus_claim"] = "Claim {amount} bonus chips";
$lang["casino_bonus_received"] = "{amount} chips received!";
$lang["casino_bonus_off"] = "The daily bonus is switched off.";
$lang["casino_bonus_taken"] = "You already claimed today's bonus.";
$lang["casino_history"] = "History";
$lang["casino_my_history"] = "My history";
$lang["casino_online"] = "{count} online";
$lang["casino_lobby"] = "Lobby";
$lang["casino_loading"] = "Loading...";
$lang["casino_col_time"] = "Time";
$lang["casino_col_game"] = "Game";
$lang["casino_col_action"] = "Action";
$lang["casino_col_chips"] = "Chips";
$lang["casino_col_balance"] = "Balance";
$lang["casino_col_details"] = "Details";
$lang["casino_player_fallback"] = "Player {id}";

$lang["casino_game_dice"] = "Dice (Sic Bo)";
$lang["casino_game_blackjack"] = "Blackjack (21)";

$lang["casino_action_register"] = "New player";
$lang["casino_action_login"] = "Sign in";
$lang["casino_action_logout"] = "Sign out";
$lang["casino_action_bonus"] = "Daily bonus";
$lang["casino_action_admin"] = "Admin change";
$lang["casino_action_join"] = "Joined table";
$lang["casino_action_leave"] = "Left table";
$lang["casino_action_bet"] = "Bet";
$lang["casino_action_refund"] = "Bet returned";
$lang["casino_action_double"] = "Double down";
$lang["casino_action_hit"] = "Hit";
$lang["casino_action_stand"] = "Stand";
$lang["casino_action_timeout"] = "Time up";
$lang["casino_action_roll"] = "Roll";
$lang["casino_action_deal"] = "Deal";
$lang["casino_action_win"] = "Won";
$lang["casino_action_lose"] = "Lost";
$lang["casino_action_push"] = "Push";
$lang["casino_action_blackjack"] = "Blackjack";

$lang["casino_err_invalid_bet"] = "Invalid bet.";
$lang["casino_err_no_chips"] = "Not enough chips.";
$lang["casino_err_busy"] = "The table is busy, please try again.";
$lang["casino_err_table_not_found"] = "Table not found.";
$lang["casino_err_table_closed"] = "This table is closed.";
$lang["casino_err_wait_round"] = "Wait for the next round.";
$lang["casino_err_unknown_spot"] = "Unknown bet.";
$lang["casino_err_min_bet"] = "Minimum bet is {amount}.";
$lang["casino_err_max_spot"] = "Maximum {amount} per bet.";
$lang["casino_err_max_bet"] = "Maximum bet is {amount}.";
$lang["casino_err_nothing_to_clear"] = "No bet to take back.";
$lang["casino_err_unknown_action"] = "Unknown action.";
$lang["casino_err_table_full"] = "The table is full.";
$lang["casino_err_sit_first"] = "Take a seat first.";
$lang["casino_err_not_your_turn"] = "It is not your turn.";
$lang["casino_err_double_first_two"] = "You can only double down on your first two cards.";

$lang["casino_dice_small"] = "Small";
$lang["casino_dice_big"] = "Big";
$lang["casino_dice_odd"] = "Odd";
$lang["casino_dice_even"] = "Even";
$lang["casino_dice_triple"] = "Any triple";
$lang["casino_dice_triple_hint"] = "3 of a kind";
$lang["casino_dice_per_die"] = "1:1 per die";
$lang["casino_dice_triple_rule"] = "Big, Small, Odd and Even lose when a triple comes up.";
$lang["casino_clear_bet"] = "Take back bets";
$lang["casino_players_at_table"] = "Players at the table";
$lang["casino_last_rolls"] = "Recent rolls";

$lang["casino_bj_dealer"] = "Dealer";
$lang["casino_bj_bet"] = "Bet";
$lang["casino_bj_back"] = "Take back";
$lang["casino_bj_hit"] = "Hit";
$lang["casino_bj_stand"] = "Stand";
$lang["casino_bj_double"] = "Double";
$lang["casino_bj_sit_hint"] = "Click a free seat to join.";

$lang["casino_js_error"] = "Something went wrong.";
$lang["casino_js_no_connection"] = "No connection to the server.";
$lang["casino_js_you"] = "you";
$lang["casino_js_round_n"] = "Round {n}";
$lang["casino_js_place_bet_start"] = "Place a bet to start the round";
$lang["casino_js_place_bet"] = "Place your bets...";
$lang["casino_js_rolling"] = "Rolling...";
$lang["casino_js_total"] = "Total {total}";
$lang["casino_js_you_win"] = "You win {amount}!";
$lang["casino_js_you_lose"] = "You lose {amount}";
$lang["casino_js_push"] = "Break even";
$lang["casino_js_won_toast"] = "Won: +{amount}";
$lang["casino_js_nobody"] = "Nobody yet";
$lang["casino_js_no_rolls"] = "No rolls yet";
$lang["casino_js_big"] = "big";
$lang["casino_js_small"] = "small";
$lang["casino_js_triple"] = "triple";
$lang["casino_js_rules"] = "Bet {min} - {max} · blackjack pays {payout}:1 · {soft17}";
$lang["casino_js_hits_soft17"] = "dealer hits soft 17";
$lang["casino_js_stands_soft17"] = "dealer stands on soft 17";
$lang["casino_js_take_seat"] = "Take a seat";
$lang["casino_js_free"] = "free";
$lang["casino_js_win"] = "Won";
$lang["casino_js_lose"] = "Lost";
$lang["casino_js_blackjack"] = "Blackjack!";
$lang["casino_js_bust"] = "Bust";
$lang["casino_js_waiting_bets"] = "Waiting for bets";
$lang["casino_js_betting_soon"] = "Betting... the cards will be dealt soon";
$lang["casino_js_your_bet_wait"] = "Your bet: {amount} · waiting for the other players";
$lang["casino_js_your_turn"] = "Your turn!";
$lang["casino_js_turn_of"] = "{name}'s turn";
$lang["casino_js_dealer_result"] = "Dealer: {value}";
$lang["casino_js_dealer_bust"] = "Dealer: {value} (bust)";
$lang["casino_js_online"] = "{count} online";
$lang["casino_js_shaker_title"] = "SIC BO";
$lang["casino_js_tap_skip"] = "Tap to skip";
$lang["casino_js_tap_continue"] = "Tap to continue";

// roulette
$lang["casino_game_roulette"] = "Roulette";
$lang["casino_action_spin"] = "Spin";
$lang["casino_rl_low"] = "1 - 18";
$lang["casino_rl_high"] = "19 - 36";
$lang["casino_rl_dozen_1"] = "1st 12";
$lang["casino_rl_dozen_2"] = "2nd 12";
$lang["casino_rl_dozen_3"] = "3rd 12";
$lang["casino_rl_zero_rule"] = "On 0 every outside bet loses. A number pays 35:1, dozens and columns 2:1, the rest 1:1.";
$lang["casino_last_numbers"] = "Recent numbers";
$lang["casino_js_red"] = "Red";
$lang["casino_js_black"] = "Black";
$lang["casino_js_green"] = "Green";
$lang["casino_js_odd"] = "Odd";
$lang["casino_js_even"] = "Even";
$lang["casino_js_spinning"] = "The wheel is spinning...";
$lang["casino_js_no_more_bets"] = "No more bets";
$lang["casino_js_wheel_title"] = "ROULETTE";

// confirm before leaving
$lang["casino_js_confirm_logout"] = "Are you sure you want to sign out?";
$lang["casino_js_confirm_leave"] = "You still have a bet or a seat at this table. Are you sure you want to leave?";
$lang["casino_js_confirm_leave_hint"] = "Open bets are returned; a hand already in play stands automatically.";
$lang["casino_js_yes"] = "Yes";
$lang["casino_js_cancel"] = "Cancel";

// dice board sections
$lang["casino_dice_sec_main"] = "Big · Small · Odd · Even";
$lang["casino_dice_sec_doubles"] = "Specific double";
$lang["casino_dice_sec_triples"] = "Specific triple";
$lang["casino_dice_sec_totals"] = "Total";
$lang["casino_dice_sec_combos"] = "Two-dice combination";
$lang["casino_dice_sec_singles"] = "Single number";
$lang["casino_dice_singles_rule"] = "Pick a number: pays 1:1, {two}:1 or {three}:1 when it shows on one, two or three dice.";

// win / loss record at the table
$lang["casino_my_records"] = "My results";
$lang["casino_rec_round"] = "Round";
$lang["casino_rec_result"] = "Result";
$lang["casino_rec_bet"] = "Bet";
$lang["casino_rec_net"] = "Win / loss";
$lang["casino_js_rec_total"] = "Last {n}:";
$lang["casino_js_rec_none"] = "You have not played at this table yet.";

// animation and sound buttons
$lang["casino_anim_toggle"] = "Animations on/off";
$lang["casino_sound_toggle"] = "Sound on/off";

// fruit machine
$lang["casino_game_slot"] = "Fruit machine";
$lang["casino_action_jackpot"] = "Jackpot";
$lang["casino_sl_jackpot"] = "JACKPOT";
$lang["casino_sl_line_bet"] = "Bet per line";
$lang["casino_sl_total_bet"] = "Total bet";
$lang["casino_sl_spin"] = "SPIN";
$lang["casino_sl_paytable"] = "Paytable";
$lang["casino_sl_cherry2"] = "2 cherries from the left";
$lang["casino_sl_cherry1"] = "1 cherry on the left";
$lang["casino_sl_rule"] = "Choose 1 to 5 win lines (line 1 is the middle one). A line pays x times the bet per line. 777 on the middle line also wins the jackpot: the whole pot at the maximum bet per line, otherwise a share in proportion.";
$lang["casino_sl_recent"] = "Recent wins on this machine";
$lang["casino_js_sl_spinning"] = "Spinning...";
$lang["casino_js_sl_press_spin"] = "Choose your bet and press SPIN (or the space bar)";
$lang["casino_js_sl_no_win"] = "No win, try again";
$lang["casino_js_sl_line"] = "Line {n}";
$lang["casino_js_sl_win"] = "You win {amount}";
$lang["casino_js_sl_big_win"] = "BIG WIN +{amount}";
$lang["casino_js_sl_jackpot_win"] = "JACKPOT! +{amount}";

// Texas Hold'em
$lang["casino_game_holdem"] = 'Texas Hold\'em Poker';
$lang["casino_pk_rules"] = 'No limit Texas Hold\'em · blinds {sb}/{bb} · buy-in {min} - {max}';
$lang["casino_action_buyin"] = 'Buy-in';
$lang["casino_action_cashout"] = 'Cash out';
$lang["casino_action_blind"] = 'Blind';
$lang["casino_action_fold"] = 'Fold';
$lang["casino_action_check"] = 'Check';
$lang["casino_action_call"] = 'Call';
$lang["casino_action_raise"] = 'Raise';
$lang["casino_action_allin"] = 'All-in';
$lang["casino_action_rake"] = 'Rake';
$lang["casino_err_pk_buyin"] = 'The buy-in must be between {min} and {max}.';
$lang["casino_err_pk_cannot_check"] = 'You cannot check, there is a bet.';
$lang["casino_err_pk_min_raise"] = 'Raise to at least {amount}.';
$lang["casino_err_pk_cannot_raise"] = 'You cannot raise again now.';
$lang["casino_js_pk_fold"] = 'Fold';
$lang["casino_js_pk_check"] = 'Check';
$lang["casino_js_pk_call"] = 'Call {amount}';
$lang["casino_js_pk_bet"] = 'Bet {amount}';
$lang["casino_js_pk_raise"] = 'Raise to {amount}';
$lang["casino_js_pk_allin"] = 'All-in';
$lang["casino_js_pk_min"] = 'Min';
$lang["casino_js_pk_half_pot"] = '½ pot';
$lang["casino_js_pk_pot"] = 'Pot';
$lang["casino_js_pk_pot_total"] = 'Pot: {amount}';
$lang["casino_js_pk_last_call"] = 'Call';
$lang["casino_js_pk_last_raise"] = 'Raise';
$lang["casino_js_pk_buyin_title"] = 'How many chips do you bring to the table?';
$lang["casino_js_pk_buyin_hint"] = 'Between {min} and {max}. What you still have when you stand up goes back to your balance.';
$lang["casino_js_pk_sit"] = 'Sit down';
$lang["casino_js_pk_sit_hint"] = 'Click an empty seat to join';
$lang["casino_js_pk_waiting"] = 'Waiting for players (at least 2)';
$lang["casino_js_pk_next_hand"] = 'The next hand starts soon...';
$lang["casino_js_pk_sitting_out"] = 'Plays from the next hand';
$lang["casino_js_pk_leaving"] = 'Leaves after this hand';
$lang["casino_js_pk_wins"] = '{name} wins {amount}';
$lang["casino_js_pk_with"] = 'with {hand}';
$lang["casino_js_pk_your_hand"] = 'Your hand: {hand}';
$lang["casino_js_pk_blinds"] = 'Blinds {sb}/{bb}';
$lang["casino_js_hand_high_card"] = 'High card';
$lang["casino_js_hand_pair"] = 'Pair';
$lang["casino_js_hand_two_pair"] = 'Two pair';
$lang["casino_js_hand_trips"] = 'Three of a kind';
$lang["casino_js_hand_straight"] = 'Straight';
$lang["casino_js_hand_flush"] = 'Flush';
$lang["casino_js_hand_full_house"] = 'Full house';
$lang["casino_js_hand_quads"] = 'Four of a kind';
$lang["casino_js_hand_straight_flush"] = 'Straight flush';
$lang["casino_js_hand_royal_flush"] = 'Royal flush';

// bingo
$lang["casino_game_bingo"] = 'Bingo';
$lang["casino_action_line"] = 'Line';
$lang["casino_action_bingo"] = 'Bingo';
$lang["casino_err_bg_max_cards"] = 'At most {max} cards per round.';
$lang["casino_bg_pot"] = 'Prize pot';
$lang["casino_bg_line_prize"] = 'First line';
$lang["casino_bg_full_prize"] = 'Full card (bingo)';
$lang["casino_bg_my_cards"] = 'My cards';
$lang["casino_bg_board"] = 'Called numbers';
$lang["casino_bg_cancel"] = 'Return cards';
$lang["casino_bg_history"] = 'Previous rounds';
$lang["casino_bg_rules"] = 'A card costs {price}. Numbers are marked automatically. The first line (row, column or diagonal) wins {line}% of the pot, the first full card the rest. The pot is {payout}% of the cards sold; winners on the same ball share.';
$lang["casino_js_bg_buy_n"] = '+{n} card ({amount})';
$lang["casino_js_bg_buy_start"] = 'Buy a card to start the round';
$lang["casino_js_bg_buying"] = 'Buying cards... the draw starts soon';
$lang["casino_js_bg_drawing"] = 'Draw: ball {n} of 75';
$lang["casino_js_bg_line_won"] = 'LINE! +{amount}';
$lang["casino_js_bg_bingo_won"] = 'BINGO! +{amount}';
$lang["casino_js_bg_line_by"] = 'Line: {names}';
$lang["casino_js_bg_full_by"] = 'Bingo: {names}';
$lang["casino_js_bg_line_badge"] = 'Line';
$lang["casino_js_bg_to_go"] = '{n} to go';
$lang["casino_js_bg_no_cards"] = 'You have no cards this round.';
$lang["casino_js_bg_cards_n"] = '{n} cards';
$lang["casino_js_bg_round_line"] = 'Round {round}: {balls} balls';

// what a bet can win
$lang["casino_js_can_win"] = 'wins +{amount}';
$lang["casino_js_can_win_max"] = 'up to +{amount}';
$lang["casino_js_sl_jackpot_short"] = 'jackpot';

// solo tables
$lang["casino_solo"] = 'Solo';
$lang["casino_err_solo_no_bets"] = 'Place a bet first.';
$lang["casino_js_solo_go_dice"] = 'Roll';
$lang["casino_js_solo_go_roulette"] = 'Spin';
$lang["casino_js_solo_hint"] = 'Place your bets and start whenever you like, no waiting';
$lang["casino_js_solo_ready"] = 'Ready? Press the button (or the space bar)';

// solo blackjack
$lang["casino_js_solo_go_bj"] = 'Deal';

// fruit machine lines
$lang["casino_sl_lines"] = 'Lines';

// bingo record
$lang["casino_js_bg_balls"] = '{n} balls';

// Sic Bo casino board
$lang["casino_dice_layout_casino"] = 'Casino table';
$lang["casino_dice_layout_list"] = 'By section';
$lang["casino_dice_pays"] = '1 pays {n}';
$lang["casino_dice_small_desc"] = '4 to 10 · any triple loses';
$lang["casino_dice_big_desc"] = '11 to 17 · any triple loses';
$lang["casino_dice_double_n"] = 'Double {n}';
$lang["casino_dice_triple_n"] = 'Triple {n}';
$lang["casino_dice_undo_last"] = 'Clear last bet';
$lang["casino_dice_double_bets"] = 'Double bet';
$lang["casino_dice_rebet"] = 'Rebet';

// webmaster login
$lang["casino_login_backoffice"] = 'Continue as webmaster ({name})';

// 
$lang["casino_js_win_congrats"] = 'Congratulations, you win {amount}';

// 
$lang["casino_fullscreen"] = 'Full screen';

// 
$lang["casino_game_doudizhu"] = 'Dou Dizhu (Landlord)';
$lang["casino_ddz_base"] = 'Base';
$lang["casino_ddz_bid"] = 'Bid';
$lang["casino_ddz_mult"] = 'Multiplier';
$lang["casino_ddz_rules"] = 'Three players: the landlord against two farmers. Bid 1 to 3 points; the highest bidder becomes landlord and takes the 3 bottom cards. The first to play all their cards wins for their side. Score = base x bid, x{bomb} per bomb, x{rocket} for the rocket (both jokers), x2 for a spring. Buy-in {min} - {max}.';
$lang["casino_ddz_bot_1"] = 'Computer A';
$lang["casino_ddz_bot_2"] = 'Computer B';
$lang["casino_err_ddz_bid"] = 'Bid higher than the current bid (1 to 3), or pass.';
$lang["casino_err_ddz_must_lead"] = 'You lead: play some cards.';
$lang["casino_err_ddz_cards"] = 'You do not have those cards.';
$lang["casino_err_ddz_invalid"] = 'These cards are not a valid play.';
$lang["casino_err_ddz_lower"] = 'Play the same kind of play, but higher (or a bomb).';
$lang["casino_err_ddz_pick"] = 'Pick the cards you want to play first.';
$lang["casino_err_ddz_stack"] = 'You do not have enough chips at the table for the base stake.';
$lang["casino_action_bid"] = 'Bid';
$lang["casino_action_landlord"] = 'Landlord';
$lang["casino_action_play"] = 'Cards played';
$lang["casino_action_pass"] = 'Pass';
$lang["casino_js_ddz_pass"] = 'Pass';
$lang["casino_js_ddz_bid_n"] = '{n} point';
$lang["casino_js_ddz_no_bid"] = 'No bid';
$lang["casino_js_ddz_landlord"] = 'Landlord';
$lang["casino_js_ddz_farmer"] = 'Farmer';
$lang["casino_js_ddz_auto_on"] = 'Auto play';
$lang["casino_js_ddz_auto"] = 'Auto play';
$lang["casino_js_ddz_auto_off"] = 'Play myself';
$lang["casino_js_ddz_solo_start"] = 'Take a seat and play against two computer players.';
$lang["casino_js_ddz_solo_ready"] = 'Ready? Press Deal.';
$lang["casino_js_ddz_wait_players"] = 'Waiting for three players...';
$lang["casino_js_ddz_starting"] = 'Dealing the cards...';
$lang["casino_js_ddz_your_bid"] = 'Your bid: do you want to be the landlord?';
$lang["casino_js_ddz_bidding"] = '{name} is bidding...';
$lang["casino_js_ddz_landlord_wins"] = 'The landlord wins';
$lang["casino_js_ddz_farmers_wins"] = 'The farmers win';
$lang["casino_js_ddz_spring"] = 'Spring!';
$lang["casino_js_ddz_solo_sit"] = 'Take a seat';
$lang["casino_js_ddz_next_hand"] = 'Next hand';
$lang["casino_js_ddz_deal"] = 'Deal';
$lang["casino_js_ddz_hint"] = 'Hint';
$lang["casino_js_ddz_play"] = 'Play';
$lang["casino_js_ddz_no_hint"] = 'Nothing beats it: pass.';

// 
$lang["casino_game_chess"] = 'Chess';
$lang["casino_game_xiangqi"] = 'Chinese chess (Xiangqi)';
$lang["casino_err_board_move"] = 'That move is not allowed.';
$lang["casino_board_bot_easy"] = 'Computer (easy)';
$lang["casino_board_bot_normal"] = 'Computer (normal)';
$lang["casino_board_bot_hard"] = 'Computer (hard)';
$lang["casino_board_rules_chess"] = 'Stake {stake} per game; the winner gets both stakes, a draw gives the stake back. Checkmate, resigning, running out of time or leaving loses. Draw: stalemate, 50 moves, threefold repetition, too little material or agreed.';
$lang["casino_board_rules_xiangqi"] = 'Stake {stake} per game; the winner gets both stakes, a draw gives the stake back. A player with no move left (mate or no move) loses, as does resigning, running out of time or leaving. Draw: threefold repetition, 60 moves without a capture or no attacking pieces left.';
$lang["casino_js_board_moves"] = 'Moves';
$lang["casino_js_board_moves_short"] = 'moves';
$lang["casino_js_board_white"] = 'White';
$lang["casino_js_board_black"] = 'Black';
$lang["casino_js_board_red"] = 'Red';
$lang["casino_js_board_away"] = 'away';
$lang["casino_js_board_waiting"] = 'Waiting for an opponent...';
$lang["casino_js_board_starting"] = 'The game is starting...';
$lang["casino_js_board_your_move"] = 'Your move';
$lang["casino_js_board_check"] = 'Check!';
$lang["casino_js_board_draw"] = 'Draw';
$lang["casino_js_board_you_won"] = 'You won!';
$lang["casino_js_board_you_lost"] = 'You lost';
$lang["casino_js_board_wins"] = '{name} wins';
$lang["casino_js_board_reason_checkmate"] = 'checkmate';
$lang["casino_js_board_reason_stalemate"] = 'stalemate';
$lang["casino_js_board_reason_stalemate_loss"] = 'no move left';
$lang["casino_js_board_reason_fifty"] = '50 moves';
$lang["casino_js_board_reason_sixty"] = '60 moves without a capture';
$lang["casino_js_board_reason_repetition"] = 'threefold repetition';
$lang["casino_js_board_reason_material"] = 'too little material';
$lang["casino_js_board_reason_resign"] = 'resigned';
$lang["casino_js_board_reason_time"] = 'out of time';
$lang["casino_js_board_reason_abandon"] = 'left the game';
$lang["casino_js_board_reason_agreed"] = 'agreed';
$lang["casino_js_board_resign"] = 'Resign';
$lang["casino_js_board_resign_confirm"] = 'Really resign? You lose your stake.';
$lang["casino_js_board_offer_draw"] = 'Offer a draw';
$lang["casino_js_board_accept_draw"] = 'Accept the draw';
$lang["casino_js_board_draw_offered"] = 'you offered a draw';
$lang["casino_js_board_draw_wanted"] = 'your opponent offers a draw';
$lang["casino_js_board_declined"] = 'the computer plays on';
$lang["casino_js_board_solo_hint"] = 'Pick your colour and the level, and play the computer.';
$lang["casino_js_board_play_as"] = 'I play';
$lang["casino_js_board_level"] = 'Level (win)';
$lang["casino_js_board_level_1"] = 'Easy';
$lang["casino_js_board_level_2"] = 'Normal';
$lang["casino_js_board_level_3"] = 'Hard';
$lang["casino_js_board_start"] = 'Start a game (stake {stake})';
$lang["casino_js_board_promote"] = 'Promote to';
$lang["casino_js_ddz_bomb_fx"] = 'BOMB! x{n}';
$lang["casino_js_ddz_rocket_fx"] = 'ROCKET! x{n}';
