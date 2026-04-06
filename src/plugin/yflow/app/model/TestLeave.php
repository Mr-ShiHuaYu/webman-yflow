<?php

namespace plugin\yflow\app\model;

use Yflow\impl\orm\laravel\FlowInstanceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use support\Model;

/**
 * OA 请假申请 Model
 *
 * @package app\model
 */
class TestLeave extends Model
{
    protected $connection = 'plugin.admin.mysql';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_leave';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The name of the created at column.
     *
     * @var string
     */
    const CREATED_AT = 'create_time';

    /**
     * The name of the updated at column.
     *
     * @var string
     */
    const UPDATED_AT = 'update_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'reason',
        'start_time',
        'end_time',
        'day',
        'instance_id',
        'node_code',
        'node_name',
        'node_type',
        'flow_status',
        'create_by',
        'create_time',
        'update_by',
        'update_time',
        'del_flag',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string', // id必须写成string类型,否则,会因为前端js的精度丢失
        'type' => 'integer',
        'day' => 'integer',
        'instance_id' => 'string',
        'node_type' => 'integer',
        'del_flag' => 'string',
        'start_time' => 'datetime:Y-m-d',
        'end_time' => 'datetime:Y-m-d',
        'create_time' => 'datetime:Y-m-d H:i:s',
        'update_time' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Hidden attributes.
     *
     * @var array
     */
    protected $hidden = [
        'del_flag',
    ];

    /**
     * 访问器（自动转换为数组）
     *
     * @var array
     */
    protected $appends = [
        'additional_handler',
    ];


    /**
     * 关联流程实例
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(FlowInstanceModel::class, 'instance_id', 'id');
    }


    /**
     * 额外处理人（动态属性，不存储在数据库）
     *
     * @var array|null
     */
    protected ?array $additionalHandler = null;

    /**
     * 设置额外处理人（修改器）
     *
     * @param array|null $additionalHandler
     * @return $this
     */
    public function setAdditionalHandlerAttribute(?array $additionalHandler): self
    {
        $this->additionalHandler = $additionalHandler;
        return $this;
    }

    /**
     * 获取额外处理人（访问器）
     *
     * @return array|null
     */
    public function getAdditionalHandlerAttribute(): ?array
    {
        return $this->additionalHandler;
    }

}
