<?php
namespace Myhamdani\Shelpers;

use Exception;
use Yajra\DataTables\Facades\DataTables;

class BlueprintShelpers {

    /**
     * @param $request
     * @param $model
     * @param $select
     * @param $currentUser
     * @param $source
     * @return array
     * @throws Exception
     */
    public static function find($request, $model, $select, $currentUser, $source): array
    {
        $params = $request->all();
        $metadata = null;

        try {
            $data = $model;
            // if (isset($currentUser) && $currentUser['level'] == 'member') $data->where('CREATED_BY', $currentUser['user']);
            if ($select != '*') $data = $data->select($select);
            if (isset($params['collect'])) {
                $collect = explode(',', $params['collect']);
                if ((count($collect) > 0)) foreach($collect as $value) $data = $data->with($value);
            }
            if (isset($params['where'])) {
                $where = json_decode($params['where']);
                foreach($where as $key => $value) {
                    if (is_object($value)) {
                        foreach($value as $operation => $subValue) {
                            if ($operation == 'contains') $data->where($key, 'like', '%' . $subValue . '%');
                            else if ($operation == 'startsWith') $data->where($key, 'like', '%' . $subValue);
                            else if ($operation == 'endsWith') $data->where($key, 'like', $subValue . '%');
                            else if ($operation == '!=') $data->whereNotIn($key, $subValue);
                            else if ($operation == 'in') $data->whereIn($key, $subValue);
                            else if ($operation == 'nin') $data->whereNotIn($key, $subValue);
                            else $data->where($key, $operation, $subValue);
                        }
                    } else {
                        if (is_array($value)) $data->whereIn($key, $value);
                        else $data->where($key, $value);
                    }
                }
            }
            if (isset($params['sort'])) {
                $sortExplodeDelimeter = explode(',', $params['sort']);
                if (count($sortExplodeDelimeter) > 0) foreach ($sortExplodeDelimeter as $value) $data = self::formatSort($data, $value);
                else $data = self::formatSort($data, $params['sort']);
            }
            if (isset($params['skip']) && isset($params['limit'])) {
                $data = $data->skip((int)$params['skip'])->take((int)$params['limit']);
                $metadata = (object)array("skip" => (int)$params['skip'], "limit" => (int)$params['limit'], "nowrows" => $data->count());
            }

            if ($source == 'datatables') {
                return array('data' => Datatables::of($data)
                    ->with([
                        "recordsTotal" => $metadata->nowrows,
                        "recordsFiltered" => count($data->get()),
                    ])
                    ->make(true)->original);
            }
            $data = $data->get();
            return array("data" => $data, "metadata" => $metadata);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * @param $id
     * @param $model
     * @return mixed
     * @throws Exception
     */
    public static function findOne($id, $model) {
        try {
            $data = $model->find($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
        return $data;
    }

    /**
     * @param $data
     * @param $value
     * @return mixed
     */
    private static function formatSort($data, $value) {
        $sortExplodeSpace = explode(' ', trim($value));
        $sortValue = 'desc';
        if (isset($sortExplodeSpace[1])) $sortValue = $sortExplodeSpace[1];
        return $data->orderBy($sortExplodeSpace[0], $sortValue);
    }
}
