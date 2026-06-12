<?php

namespace App\Http\Repositories;


interface ICommonRepository
{
    public function getById($id);

    public function getAll();

    public function deleteById($id);

    public function create($data);

    public function insert($data);

    public function getDocs($params = [], $select = null, $orderBy = [], $with = []);

    public function updateWhere($where = [], $update = []);

    public function deleteWhere($where = [], $isForce = false);

    public function exists($where = [], $relation = []);
    public function countWhere($where = [], $relation = []);

    public function randomWhere($quantity, $where = [], $relation = []);

    public function whereFirst($where = [], $relation = []);

    public function selectWhere($select, $where, $relation = [], $paginate = 0);

    public function limitWhere($quantity, $where = [], $relation = []);
}
