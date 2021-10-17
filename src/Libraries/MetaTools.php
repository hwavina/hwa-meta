<?php

namespace Hwavina\HwaMeta\Libraries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MetaTools extends Model
{
    use HasFactory;

    public $type;

    public $metaFieldId;

    public $metaFieldObjectType; //Column of object meta type

    public $allow_type;

    public $table;

    public function __construct($type)
    {
        $this->type = $type;

        $this->allow_type = config('hwa_meta.allow_type');

        $this->metaFieldId = $this->allow_type[$this->type][0];

        $this->metaFieldObjectType = $this->allow_type[$this->type][1];

        $this->table = $this->type . '_metas';
    }

    /**
     * var $_instance for recall itself
     */
    public static $_instance;

    /**
     * Check instance and reset it
     *
     * @return MetaTools $_instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Retrieve data meta field for a table meta.
     *
     * @param int $object_id it is primarykey of table meta.
     * @param $meta_key
     * @param bool $single Whether to return a single value.
     * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
     */
    public function getMeta($object_id, $meta_key, $single = true)
    {
        if (!is_numeric($object_id)) {
            return false;
        }

        $object_id = abs(intval($object_id));
        if (!$object_id) {
            return false;
        }

        if (!isset($this->allow_type[$this->type]))
            return false;

        //Table data for access
        $table = $this->table;

        //Use App Data get meta data
        $metaData = DB::table($table)
            ->where($this->metaFieldObjectType, $object_id)
            ->where('meta_key', $meta_key);

        if ($single) {
            $metaData = $metaData->first();

            if ($metaData && isset($metaData->meta_value)) {
                $meta_value = $metaData->meta_value;

                return HwaTools::maybe_unserialize($meta_value);
            } else {
                return false;
            }
        } else {
            $meta_values = array();
            $metaData = $metaData->get();

            foreach ($metaData->toArray() as $meta) {
                if ($meta['meta_value']) {
                    $meta_values[] = HwaTools::maybe_unserialize($meta['meta_value']);
                }
            }

            return $meta_values;
        }
    }


    /**
     * Add metadata for the specified object.
     *
     * @param int $object_id ID of the object metadata is for
     * @param string $meta_key Metadata key
     * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
     * @param bool $unique Optional, default is false.
     *                           Whether the specified metadata key should be unique for the object.
     *                           If true, and the object already has a value for the specified metadata key,
     *                           no change will be made.
     * @return int|false The meta ID on success, false on failure.
     */
    public function addMeta($object_id, $meta_key, $meta_value, $unique = false)
    {
        if (!$meta_key || !is_numeric($object_id)) {
            return false;
        }

        $object_id = HwaTools::absint($object_id);
        if (!$object_id) {
            return false;
        }

        $table = $this->table;
        if (!$table) {
            return false;
        }

        // expected_slashed ($meta_key)
        $meta_key = HwaTools::unslash($meta_key);
        $meta_value = HwaTools::unslash($meta_value);
        $column = $this->metaFieldObjectType;

        //Use App Data get meta data
        if ($unique && DB::table($this->table)
                ->where([
                    ['meta_key', '=', $meta_key],
                    [$column, '=', $object_id]
                ])
                ->first())
            return false;

        $meta_value = HwaTools::maybe_serialize($meta_value);

        $data_insert = array(
            $column => $object_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value
        );

        $result = DB::table($this->table)->insert($data_insert);

        if (!$result)
            return false;

        return (int)DB::getPdo()->lastInsertId();
    }

    /**
     * Delete metadata for the specified object.
     *
     * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
     * @param int $object_id ID of the object metadata is for
     * @param string $meta_key Metadata key
     * @param mixed $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
     *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
     *                           Pass `null, `false`, or an empty string to skip this check. (For backward compatibility,
     *                           it is not possible to pass an empty string to delete those entries with an empty string
     *                           for a value.)
     * @param bool $delete_all Optional, default is false. If true, delete matching metadata entries for all objects,
     *                           ignoring the specified object_id. Otherwise, only delete matching metadata entries for
     *                           the specified object_id.
     * @return bool True on successful delete, false on failure.
     */
    public function deleteMeta($object_id, $meta_key, $meta_value = '', $delete_all = false)
    {
        if (!$meta_key || !is_numeric($object_id) && !$delete_all) {
            return false;
        }

        $object_id = HwaTools::absint($object_id);
        if (!$object_id && !$delete_all) {
            return false;
        }

        $table = $this->table;
        if (!$table) {
            return false;
        }

        $id_column = $this->metaFieldId;
        // expected_slashed ($meta_key)
        $meta_key = HwaTools::unslash($meta_key);
        $meta_value = HwaTools::unslash($meta_value);
        $meta_value = HwaTools::maybe_serialize($meta_value);

        $buildQuery = DB::table($this->table)
            ->where('meta_key', $meta_key);

        if (!$delete_all)
            $buildQuery = $buildQuery->where($this->metaFieldObjectType, $object_id);

        if ('' !== $meta_value && null !== $meta_value && false !== $meta_value)
            $buildQuery = $buildQuery->where('meta_value', $meta_value);

        $buildQuery->delete();

        return true;
    }

    /**
     * Update metadata for the specified object. If no value already exists for the specified object
     * ID and metadata key, the metadata will be added.
     *
     * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
     * @param int $object_id ID of the object metadata is for
     * @param string $meta_key Metadata key
     * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
     * @param mixed $prev_value Optional. If specified, only update existing metadata entries with
     *                             the specified value. Otherwise, update all entries.
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
     */
    public function updateMeta($object_id, $meta_key, $meta_value, $prev_value = '')
    {
        if (!$meta_key || !is_numeric($object_id)) {
            return false;
        }

        $object_id = HwaTools::absint($object_id);
        if (!$object_id) {
            return false;
        }

        $table = $this->table;
        if (!$table) {
            return false;
        }

        $id_column = $this->metaFieldId;

        // expected_slashed ($meta_key)
        $raw_meta_key = $meta_key;
        $meta_key = HwaTools::unslash($meta_key);
        $passed_value = $meta_value;
        $meta_value = HwaTools::unslash($meta_value);

        // Compare existing value to new value if no prev value given and the key exists only once.
        if (empty($prev_value)) {
            $old_value = $this->getMeta($object_id, $meta_key);
            $old_value = (array)$old_value;

            if (count($old_value) == 1) {
                if (isset($old_value[0]) && $old_value[0] === $meta_value)
                    return false;
            }
        }

        $column = $this->metaFieldObjectType;

        $meta_ids = DB::table($this->table)
            ->where([
                ['meta_key', '=', $meta_key],
                [$column, '=', $object_id]
            ])->value($id_column);


        if (empty($meta_ids)) {
            return $this->addMeta($object_id, $raw_meta_key, $passed_value);
        }

        $meta_value = HwaTools::maybe_serialize($meta_value);

        $data = compact('meta_value');

        $where = [
            [$column, '=', $object_id],
            ['meta_key', '=', $meta_key]
        ];

        if (!empty($prev_value)) {
            $prev_value = HwaTools::maybe_serialize($prev_value);
            $where[] = ['meta_value', '=', $prev_value];
        }

        $result = DB::table($this->table)->where($where)->update($data);

        if (!$result)
            return false;

        return true;
    }
}
