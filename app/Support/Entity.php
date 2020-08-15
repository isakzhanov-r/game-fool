<?php


namespace App\Support;


use App\Contracts\EntityContract;
use App\Contracts\MutatorContract;
use App\Contracts\TypedContract;
use App\Support\Traits\MutatorValues;
use App\Support\Traits\TypedValue;

abstract class Entity implements EntityContract, TypedContract, MutatorContract
{
    use TypedValue, MutatorValues;

    public $public = [];

    protected $origins = [];

    protected $casts = [];

    protected $fields = [];

    public function __construct(array $attributes = [])
    {
        $this->syncPublic();
        $this->setPublicFields();
        $this->fill($attributes);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);

        return $this;
    }

    public function get($key)
    {
        if (is_null($key)) {
            return;
        }

        if (!$this->isFillable($key)) {
            throw new \Exception("Property [{$key}] does not exist on this.");
        }

        if (array_key_exists($key, $this->public)) {

            $value = $this->public[$key];

            if ($this->hasGetMutator($key)) {
                return $this->getMutator($key, $value);
            }

            return $value;
        }
    }

    public function set($key, $value)
    {
        if (!$this->isFillable($key)) {
            throw new \Exception("Property [{$key}] does not exist on this.");
        }

        if ($this->hasSetMutator($key)) {
            $value = $this->setMutator($key, $value);
        }

        $this->public[$key] = $this->castAttribute($this->getCastType($key), $value);

        $this->syncOrigins();

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function isFillable($key): bool
    {
        if (in_array($key, $this->getFields())) {
            return true;
        }

        return false;
    }

    final protected function syncPublic()
    {
        $this->public = array_fill_keys($this->fields, null);

        return $this;
    }

    final protected function syncOrigins()
    {
        $this->origins = $this->public;
    }

    final protected function setPublicFields()
    {
        foreach ($this->casts as $key => $cast_type) {
            $this->set($key, null);
        }
    }

    final protected function fieldsFromArray(array $attributes): array
    {
        if (count($this->fields) > 0) {
            return array_intersect_key($attributes, array_flip($this->fields));
        }

        return $attributes;
    }

    final protected function fill(array $attributes)
    {
        foreach ($this->fieldsFromArray($attributes) as $key => $value) {
            if ($this->isFillable($key)) {
                $this->set($key, $value);
            } else {
                throw new \Exception(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }
        }

        return $this;
    }

    final protected function castAttribute($key, $value = null)
    {
        if (class_exists($key)) {
            if (is_null($value)) {
                return new $key();
            }
            $this->isInstance($key, $value);
        }
        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return $this->isInteger($value);
            case 'str':
            case 'string':
                return $this->isString($value);
            case 'bool':
            case 'boolean':
                return $this->isBoolean($value);
            case 'array':
                return $this->isArray($value);
            case 'json':
                return $this->isJSON($value);
            case 'object':
                return $this->isObject($value);
            default:
                return $value;

        }
    }

    final protected function getCastType($key)
    {
        if (array_key_exists($key, $this->casts)) {
            return $this->casts[$key];
        }

        return null;
    }
}
