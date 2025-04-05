<?php

class Applog extends Ajax_Controller
{

    public function json_data()
    {
        $search = CIInput()->post("search");
        $length = CIInput()->post("length");
        $start = CIInput()->post("start");
        $draw = CIInput()->post("draw");
        $order = CIInput()->post("order");
        $columns = CIInput()->post("columns");

        if (empty($order)) {
            $order[0]['column'] = 0;
            $order[0]['dir'] = 'desc';
        }

        AppLogModel::$sqlSelect =
            [
                [UserModel::$table => 'emailaddress']
            ];
        AppLogModel::$sqlJoin =
            [
                [UserModel::$table => UserModel::$primaryKey]
            ];

        AppLogModel::$joinLeftTables = [UserModel::$table];



        $totaldata = AppLogModel::getTotal();

        $arrcolumns = array(
            0 => 'log_id',
            1 => 'date',
            2 => 'user.emailaddress',
            3 => 'description',
            4 => 'path'
        );

        //        if (empty($search) === false) {
        //            $data_or_like = [];
        //            foreach ($arrcolumns as $value) {
        //                $data_or_like[$value] = $search['value'];
        //            }
        //            AppLogModel::$sqlOrLike = $data_or_like;
        //        }

        if (!empty($columns)) {
            $data_where = [];
            $data_like = [];
            foreach ($columns as $value) {
                $valuesearch = $value["search"]['value'];
                $field = $value['data'];
                if (!empty($valuesearch)) {
                    switch ($field) {
                        case 'emailaddress':
                            $data_like[UserModel::$table . '.emailaddress'] = $valuesearch;
                            break;
                        case 'date':
                            $obj = date_create(trim($valuesearch));
                            if ($obj !== false) {
                                $data_where[AppLogModel::$table . ".date >="] = date_format($obj, 'Y-m-d H:i:s');
                                $data_where[AppLogModel::$table . ".date <="] = date_format($obj, 'Y-m-d 23:59:59');
                            }
                            break;
                        default:
                            $data_like[$field] = $valuesearch;
                            break;
                    }
                }
            }

            AppLogModel::$sqlWhere =  $data_where;
            AppLogModel::$sqlLike =  $data_like;
        }

        $limit = empty($length) ? 10 : $length;
        $page = empty($start) ? 0 : $start;
        $order_by_field = $arrcolumns[$order[0]['column']];
        $order_by_type = $order[0]['dir'];

        AppLogModel::$sqlOrderBy = [$order_by_field => $order_by_type];

        //debug_as_file(AppLogModel::$sqlLike);
        $totalfiltered = AppLogModel::getTotal();
        $controller_url = AccessCheckModel::$backPath . '/applog';

        $arr_result = [];
        $listdb = AppLogModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["date"] = F_datetime::convert_datetime($rs["date"]);
            $rs["del_url"] = site_url($controller_url . "/del");
            $rs["view_url"] = site_url($controller_url . "/view/" . $rs["log_id"]);
            $rs["button"] = '<button type="button" class="btn btn-info btn-sm" data-view_link="' . $rs["view_url"] . '" data-toggle="modal" data-target="#Modal_view_detail_app_log" ><i class="fas fa-eye fa-fw"></i></button> ' . '<button type="button" class="btn btn-danger btn-sm delButton" data-search_data ="' . $rs["log_id"] . '" data-del_link="' . $rs["del_url"] . '"><i class="fas fa-times fa-fw"></i></button>';
            $arr_result[] = $rs;
        }

        $json = array(
            "draw" => intval($draw),
            "recordsTotal" => intval($totaldata),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $arr_result
        );

        exit(json_encode($json));
    }
}
