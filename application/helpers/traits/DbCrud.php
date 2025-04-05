<?php

trait DbCrud
{
    public static CI_DB_mysqli_driver $db;
    public static $table = "";
    public static $primaryKey = "id";
    public static $fieldCreatedBy = 'createdby';
    public static $fieldModifiedBy = 'modifiedby';
    public static $fieldDeletedAt = 'deleted_at';
    public static $fieldIsDel = 'is_del';

    public static $sqlSetRaw = [];

    public static $sqlSelect = [];
    public static $sqlSelectRaw = [];

    public static $sqlWhere = [];
    public static $sqlWhereRaw = [];

    public static $sqlWhereIn = [];
    public static $sqlWhereInRaw = [];

    public static $sqlLike = [];
    public static $sqlLikeRaw = [];

    public static $sqlOrderBy = [];
    public static $sqlOrderByRaw = [];

    public static $sqlJoin = [];
    public static $joinLeftTables = [];

    public static $selectOrderBy = [];
    public static $usingSoftDel = false;

    public static function sqlReset()
    {
        self::$sqlSetRaw = [];
        self::$sqlSelect = [];
        self::$sqlSelectRaw = [];
        self::$sqlWhere = [];
        self::$sqlWhereRaw = [];
        self::$sqlWhereIn = [];
        self::$sqlWhereInRaw = [];
        self::$sqlJoin = [];
        self::$sqlLike = [];
        self::$sqlLikeRaw = [];
        self::$sqlOrderBy = [];
        self::$sqlOrderByRaw = [];
        self::$joinLeftTables = [];
        self::$db->reset_query();
    }


    public static function add(array $data = []): int
    {
        if (self::$db->field_exists(self::$fieldCreatedBy, self::$table)) {
            $data[self::$fieldCreatedBy] = LoginModel::userId();
        }
        self::$db->insert(self::$table, $data);
        return self::$db->insert_id();
    }

    public static function edit(int $id = 0, array $data = []): bool
    {
        if (self::$db->field_exists(self::$fieldModifiedBy, self::$table)) {
            $data[self::$fieldModifiedBy] = LoginModel::userId();
        }

        self::$db->set($data);
        if (empty(self::$sqlSetRaw) === false) {
            foreach (self::$sqlSetRaw as $field => $value) {
                self::$db->set($field, $value, false);
            }
        }
        self::$db->where(self::$primaryKey, $id);
        return self::$db->update(self::$table);
    }

    public static function del(int $id = 0)
    {
        if (self::$usingSoftDel) {
            $data[self::$fieldIsDel] = 1;
            if (self::$db->field_exists(self::$fieldDeletedAt, self::$table)) {
                $data[self::$fieldDeletedAt] = date('Y-m-d H:i:s');
            }
            return self::edit($id, $data);
        }
        return self::$db->from(self::$table)->where(self::$primaryKey, $id)->limit(1)->delete();
    }

    public static function getAll(): array
    {
        $limit = self::getTotal();
        return self::getList($limit);
    }

    public static function getTotal(): int
    {
        self::$db->select(self::$primaryKey);
        self::$db->from(self::$table);
        if (empty(self::$sqlJoin) === false) {
            foreach (self::$sqlJoin as $result) {
                foreach ($result as $table => $field) {
                    $type = '';
                    if (in_array($table, self::$joinLeftTables)) {
                        $type = 'left';
                    }
                    self::$db->join($table, $field, $type);
                }
            }
        }

        if (empty(self::$sqlWhere) === false) {
            foreach (self::$sqlWhere as $field => $value) {
                self::$db->where($field, $value);
            }
        }

        if (empty(self::$sqlWhereRaw) === false) {
            foreach (self::$sqlWhereRaw as $field => $value) {
                self::$db->where($field, $value, false);
            }
        }

        if (empty(self::$sqlLike) === false) {
            foreach (self::$sqlLike as $field => $value) {
                self::$db->like($field, $value);
            }
        }

        if (empty(self::$sqlLikeRaw) === false) {
            foreach (self::$sqlLikeRaw as $field => $value) {
                self::$db->like($field, $value, 'both', false);
            }
        }

        if (empty(self::$sqlWhereIn) === false) {
            foreach (self::$sqlWhereIn as $field => $value) {
                self::$db->where_in($field, $value);
            }
        }

        if (empty(self::$sqlWhereInRaw) === false) {
            foreach (self::$sqlWhereInRaw as $field => $value) {
                self::$db->where_in($field, $value, false);
            }
        }

        return self::$db->count_all_results();
    }

    public static function getList(int $limit = 1, int $page = 0): array
    {
        self::$db->select(self::$table . '.*');
        if (empty(self::$sqlSelect) === false) {
            foreach (self::$sqlSelect as $result) {
                foreach ($result as $table => $field) {
                    self::$db->select($table . '.' . $field);
                }
            }
        }

        if (empty(self::$sqlSelectRaw) === false) {
            foreach (self::$sqlSelectRaw as $select) {
                self::$db->select($select, false);
            }
        }

        self::$db->from(self::$table);
        if (empty(self::$sqlJoin) === false) {
            foreach (self::$sqlJoin as $result) {
                foreach ($result as $table => $field) {
                    $type = '';
                    if (in_array($table, self::$joinLeftTables)) {
                        $type = 'left';
                    }
                    self::$db->join($table, $field, $type);
                }
            }
        }

        if (empty(self::$sqlWhere) === false) {
            foreach (self::$sqlWhere as $field => $value) {
                self::$db->where($field, $value);
            }
        }

        if (empty(self::$sqlWhereRaw) === false) {
            foreach (self::$sqlWhereRaw as $field => $value) {
                self::$db->where($field, $value, false);
            }
        }

        if (empty(self::$sqlLike) === false) {
            foreach (self::$sqlLike as $field => $value) {
                self::$db->like($field, $value);
            }
        }

        if (empty(self::$sqlLikeRaw) === false) {
            foreach (self::$sqlLikeRaw as $field => $value) {
                self::$db->like($field, $value, 'both', false);
            }
        }

        if (empty(self::$sqlWhereIn) === false) {
            foreach (self::$sqlWhereIn as $field => $value) {
                self::$db->where_in($field, $value);
            }
        }

        if (empty(self::$sqlWhereInRaw) === false) {
            foreach (self::$sqlWhereInRaw as $field => $value) {
                self::$db->where_in($field, $value, false);
            }
        }

        if (empty(self::$sqlOrderByRaw) === false) {
            foreach (self::$sqlOrderByRaw as $field => $value) {
                self::$db->order_by($field, $value, false);
            }
        }

        if (empty(self::$sqlOrderBy) === false) {
            foreach (self::$sqlOrderBy as $field => $value) {
                self::$db->order_by($field, $value);
            }
        }

        if (empty(self::$sqlOrderBy)) {
            self::$db->order_by(self::$primaryKey, "desc");
        }

        if ($page <= 0) {
            $page = 0;
        }
        self::$db->limit($limit, $page);
        $query = self::$db->get();
        return $query->result_array();
    }

    public static function getOne(): array
    {
        $arr_result = self::getList();
        if (empty($arr_result) === false) {
            return current($arr_result);
        }
        return [];
    }

    public static function getOneById(int $id = 0): array
    {
        $data[self::$primaryKey] = $id;
        return self::getOneByFields($data);
    }

    public static function getOneByField(string $field = '', $value = null): array
    {
        if (empty($field) === false && $value !== null) {
            $data[$field] = $value;
            return self::getOneByFields($data);
        }
        return [];
    }

    public static function getOneByFields(array $data = []): array
    {
        if (empty($data) === false) {
            self::$sqlWhere = $data;
        }
        return self::getOne();
    }

    public static function getAllByField(string $field = '', $value = null): array
    {
        if (empty($field) === false && $value !== null) {
            self::$sqlWhere = [];
            $data[$field] = $value;
            self::$sqlWhere = $data;
        }
        return self::getAll();
    }

    public static function fetchField(int $id = 0, string $field = ''): string
    {
        $arr = self::getOneById($id);
        if (isset($arr[$field]) && empty($arr)) {
            return "";
        }
        return $arr[$field] ?? "";
    }
}
