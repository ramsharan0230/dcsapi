<?php   

namespace App\Repositories\Eloquent;   

use App\Repositories\BaseInterface; 
use Illuminate\Database\Eloquent\Model;   
use Illuminate\Support\Facades\Schema;

class BaseRepository implements BaseInterface 
{     
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated data according to the given conditions passed.
     * @param $status
     * @param $sortBy
     * @param $limit
     * @return mixed
     */
    public function getAll($sortBy, $limit)
    {

        return $this->model->orderBy('id', $sortBy)->paginate($limit);
    }



    /**
     * Insert new row in related table.
     * @param array $data
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

  

    /**
     * Update row of given id in related table.
     * @param array $data
     * @param $id
     */
    public function update($id, array $data)
    {
        $this->model->findOrFail($id)->update($data);
        $data = $this->model->findOrFail($id);
        return $data;
    }

    /**
     * Delete row of given id in related table.
     * @param $id
     */
    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * Get data related to given id in related table.
     * @param $id
     */
    public function insert(array $data){
        return $this->model->insert($data);
    }

    public function getSpecificById($id)
    {
        $data = $this->model->findOrFail($id);
        return $data;
    }

    public function getSpecificBySlug($slug)
    {
        $data = $this->model->findOrFail($slug);
        return $data;
    }

    public function getAllWithParam(array $parameter, $path)
    {
        $columnsList = Schema::getColumnListing($this->model->getTable());
        //$is_columnExistInUserTable = false;

        $orderByColumn = "id";
        foreach ($columnsList as $columnName) {
            if ($columnName == $parameter["sort_field"]) {
                $orderByColumn = $columnName;
                break;
            }
        }
        $parameter["sort_field"] = $orderByColumn;
        if (isset($parameter["filter_field"])) {
            if (in_array($parameter["filter_field"], $columnsList)) {
                $data = $this->model->where($parameter["filter_field"], $parameter["filter_value"]);
            } else {
                $data = $this->model;
            }
        } else {
            $data = $this->model;
        }
        if (isset($parameter["q"])) {
            $searchValue = "%" . $parameter["q"] . "%";

            $data = $data->where(function ($query) use ($searchValue, $columnsList) {
                foreach ($columnsList as $key => $columnName) {

                    $query->orWhere($columnName, "like", $searchValue);
                }
            });
        }

        return $data->orderBy($orderByColumn, $parameter["sort_by"])->paginate($parameter["limit"])->withPath($path)->appends($parameter);
    }

    public function getSpecificByColumnValue($column, $value)
    {
        return $this->model->where($column, $value)->first();
    }
}