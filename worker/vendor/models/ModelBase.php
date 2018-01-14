<?php
/**
 * Model Base Level
 *
 * @author XuRongYi<xurongyi@wepiao.com>
 * @date 2015-02-07
 */

namespace Models;

use Components\Utils\Compare;
use Components\Utils\ID;

/**
 * ModelBase class.
 */
abstract class ModelBase
{
    /**
     * default object cache
     */
    protected static $instances_ = array();

    /**
     * 是否为新数据模型.
     */
    protected $isNewRecord_ = true;

    /**
     * holds the table's field values which can be accessed via the magic __get, and these fields should be defined in the static $fields property of the derived class.
     *
     * @var array
     */
    protected $fieldProperties = array();

    /**
     * 被修改过的模型字段.
     */
    private $hasChangedAttributeField_  = array();

    /**
     * 存放错误信息容器.
     */
    private $errors_ = array();

    /**
     * 校验器容器数据.
     */
    private $validators_;

    /**
     * 数据保存场景设置，默认场景有: 'insert', 'update', 'detele', 也可以自定义， 具体使用在数据检测场景.
     */
    private $scenario_ = '';

    /**
     * Abstract get model table name function.
     * 
     * @return string
     */
    abstract function getTableName();

    /**
     * Returns the primary key of the associated database table.Default set primary key is 'uid'. 
     * 
     * @return string
     */
    abstract function primaryKey();

    /**
     * To get a self single object.
     *
     * @return static
     */
    public static function model()
    {
        $class = get_called_class();
        if (!isset(static::$instances_[$class])) {
            static::$instances_[$class] = new $class;
        }
        return static::$instances_[$class];
    }

    /**
     * Magic __get method.
     *
     * You can access the field values, if filled,  directly.
     *
     * @param string $name Name.
     *
     * @return mixed
     * @throws \Exception 业务异常.
     */
    public function __get($name)
    {
        switch ($name)
        {
            default:
                if (isset($this->fieldProperties[$name])) {
                    return $this->fieldProperties[$name];
                    continue;
                }
                throw new \Exception('Try get undefined property "'.$name.'" of class '.get_called_class().'. Forgot to call fillFields ?');
                continue;
        }
    }

    /**
     * Sets value of a model field.
     *
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (
            !$this->isNewRecord_ && (
                !isset($this->fieldProperties[$name]) || (
                    $this->fieldProperties[ $name] != $value &&
                    !isset($this->hasChangedAttributeField_[ $name ])
                )
            )
        ) {
            $this->hasChangedAttributeField_[ $name ]  = $value;
        }
        $this->fieldProperties[ $name ]  = $value;
    }

    /**
     * 检测当前数据模型对象中是否有某字段存在.
     *
     * @param string $name The property name.
     *
     * @return boolean.
     */
    public function hasAttribute($name)
    {
        return isset($this->fieldProperties[ $name ]);
    }

    /**
     * 将下划线转成驼峰表字段属性map.
     *
     * @param boolean $isObverse 将key与value对换.
     *
     * @return array
     */
    public function getFieldHumpMap($isObverse = false)
    {
        if (isset($this::$fields)) {
            $formatFields = array();
            foreach ($this::$fields as $field) {
                $formatField = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))));
                if ($isObverse) {
                    $formatFields[$formatField] = $field;
                } else {
                    $formatFields[$field] = $formatField;
                }
            }
        }
         return $formatFields;
    }

    /**
     * 将驼峰格式字段转换成下划线字段.
     *
     * @param array $fields 字段.
     *
     * @return array
     */
    public function humpToUnderLineMap($fields = array())
    {
        $formatFields = array();
        if (is_string($fields)) {
            $formatFields = strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $fields));
        } else if (is_array($fields)) {
            foreach ($fields as $field) {
                $formatField[$field] = strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $field));
            }
        }
        return $formatFields;
    }

    /**
     * 获取是否是新设置的属性对象.
     *
     * @return boolean.
     */
    public function isNewRecord()
    {
        return $this->isNewRecord_;
    }

    /**
     * 保存数据之前任务, 可以在具体的model中定义不同hooker.
     *
     * @return boolean, 是否执行成功，执行成功后，才会执行保存.
     */
    protected function beforeSave()
    {
        return true;
    }

    /**
     * 保存数据之后的任务, 可以在具体的model中定义不同hooker.
     *
     * @return void.
     */
    protected function afterSave()
    {
        return null;
    }

    /**
     * 更新数据之前任务, 可以在具体的model中定义不同hooker.
     *
     * @return boolean, 是否执行成功，执行成功后才会执行更新.
     */
    protected function beforeUpdate()
    {
        return true;
    }

    /**
     * 更新数据之后的任务, 可以在具体的model中定义不同hooker.
     *
     * @return void.
     */
    protected function afterUpdate()
    {
        return null;
    }

    /**
     * 删除数据之前任务, 可以在具体的model中定义不同hooker.
     *
     * @return boolean, 是否执行成功，执行成功后才会执行删除.
     */
    protected function beforeDelete()
    {
        return true;
    }

    /**
     * 更新数据之后的任务, 可以在具体的model中定义不同hooker.
     *
     * @return void.
     */
    protected function afterDelete()
    {
        return null;
    }

    /**
     * Returns the text label for the specified attribute.
     *
     * @param string $attribute The attribute name.
     *
     * @return string the attribute label.
     */
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        if (isset($labels[$attribute])) {
            return $labels[$attribute];
        } else {
            return $this->generateAttributeLabel($attribute);
        }
    }

    /**
     * Retruns the data in fieldPropertires.
     *
     * @param mixed $names The filed name array.
     *
     * @return array.
     */
    public function getAttributes($names = null)
    {
        if ($names && !is_array($names)) {
            $nms[]  = $names;
            $names  = $nms;
        }

        if (is_array($names)) {
            $attrs = array();
            foreach ($names as $name) {
                if (isset($this::$fields) && in_array($name, $this::$fields)) {
                    $attrs[ $name ] = $this->$name;
                } else {
                    $attrs[ $name ] = null;
                }
            }
            return $attrs;
        } else {
            return $this->fieldProperties;
        }
    }

    /**
     * Returns the attribute labels.
     *
     * Attribute labels are mainly used in error messages of validation.
     * By default an attribute label is generated using {@link generateAttributeLabel}.
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions like array_merge().
     *
     * @return array attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array();
    }

    /**
     * Generates a user friendly attribute label.
     *
     * This is done by replacing underscores or dashes with blanks and
     * changing the first letter of each word to upper case.
     * For example, 'department_name' or 'DepartmentName' becomes 'Department Name'.
     *
     * @param string $name The column name.
     *
     * @return string the attribute label
     */
    public function generateAttributeLabel($name)
    {
        return ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
    }

    /**
     * 返回数据检测结果.
     *
     * @param string $attribute 某字段属性名, 否则为获取全局错误是否有定义.
     *
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        if ($attribute === null)
            return $this->errors_ !== array();
        else
            return isset($this->errors_[$attribute]);
    }

    /**
     * 返回具体错误定义，当属性名为定义，则获取所有错误信息.
     *
     * @param string $attribute 具体属性名.
     *
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null)
            return $this->errors_;
        else
            return isset($this->errors_[$attribute]) ? $this->errors_[$attribute] : array();
    }

    /**
     * 返回具体某字段错误信息.
     *
     * @param string $attribute Attribute name.
     *
     * @return string the error message. Null is returned if no error.
     */
    public function getError($attribute)
    {
        return isset($this->errors_[$attribute]) ? reset($this->errors_[$attribute]) : null;
    }

    /**
     * Adds a new error to the specified attribute.
     *
     * @param string $attribute Attribute name.
     * @param string $error     New error message.
     *
     * @return void.
     */
    public function addError($attribute, $error)
    {
        $this->errors_[$attribute][] = $error;

        return null;
    }

    /**
     * Adds a list of errors.
     *
     * @param array $errors A list of errors.
     *
     * @return void.
     */
    public function addErrors($errors)
    {
        foreach ($errors as $attribute => $error) {
            if (is_array($error)) {
                foreach ($error as $e) {
                    $this->addError($attribute, $e);
                }
            } else {
                $this->addError($attribute, $error);
            }
        }

        return null;
    }

    /**
     * Removes errors for all attributes or a single attribute.
     *
     * @param string $attribute Attribute name.
     *
     * @return void.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->errors_ = array();
        } else {
            unset($this->errors_[$attribute]);
        }

        return null;
    }

    /**
     * Return the last sql.
     *
     * @return string
     */
    public function getLastSql()
    {
        return $this->getReadDb()->getLastSql();
    }

    /**
     * Performs the validation, 执行在rule方法中定义的数据检测规则.
     *
     * @param mixed   $attributes  将校验的模型字段值，可为null, 表示检验所有.
     * @param boolean $clearErrors 是否清空现有错误信息. 
     *
     * @return boolean 是否校验成功.
     */
    protected function validate($attributes = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }
        foreach ($this->getValidators() as $validator) {
            $validator->validate($this, $attributes);
        }

        return !$this->hasErrors();
    }

    /**
     * 获取所有或某字段的校验器.
     *
     * @param string $attribute The name of the attribute whose validators should be returned. If this is null, the validators for ALL attributes in the model will be returned.
     *
     * @return array the validators applicable to the current.
     */
    public function getValidators($attribute = null)
    {
        if($this->validators_ === null)
            $this->validators_ = $this->createValidators();

        $validators = array();
        foreach ($this->validators_ as $validator) {
            // 通过场景区分应该检测那些字段(on).
            if ($validator->applyTo($this->scenario_)) {
                if ($attribute === null || in_array($attribute, $validator->attributes, true)) {
                    $validators[] = $validator;
                }
            }
        }

        return $validators;
    }

    /**
     * Creates validator objects based on the specification in {@link rules}. This method is mainly used internally.
     *
     * @return object $validators built based on {@link rules()}.
     */
    public function createValidators()
    {
        $validators = array();
        foreach ($this->rules() as $rule) {
            // attributes, validator name
            if (isset($rule[0], $rule[1])) {
                $validators[] = Validators\Validator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2));
            }
        }

        return $validators;
    }

    /**
     * Returns the validation rules for attributes.
     *
     * This method should be overridden to declare validation rules.
     * Each rule is an array with the following structure:
     * <pre>
     * array('attribute list', 'validator name', 'on'=>'scenario name', ...validation parameters...)
     * </pre>
     * where
     * <ul>
     * <li>attribute list: specifies the attributes (separated by commas) to be validated;</li>
     * <li>validator name: specifies the validator to be used. It can be the name of a model class
     *   method, the name of a built-in validator, or a validator class (or its path alias).
     *   A validation method must have the following signature:
     * <pre>
     * // $params refers to validation parameters given in the rule
     * function validatorName($attribute,$params)
     * </pre>
     *   A built-in validator refers to one of the validators declared in {@link CValidator::builtInValidators}.
     *   And a validator class is a class extending {@link CValidator}.</li>
     * <li>on: this specifies the scenarios when the validation rule should be performed.
     *   Separate different scenarios with commas. If this option is not set, the rule
     *   will be applied in any scenario that is not listed in "except". Please see {@link scenario} for more details about this option.</li>
     * <li>except: this specifies the scenarios when the validation rule should not be performed.
     *   Separate different scenarios with commas. Please see {@link scenario} for more details about this option.</li>
     * <li>additional parameters are used to initialize the corresponding validator properties.
     *   Please refer to individal validator class API for possible properties.</li>
     * </ul>
     *
     * The following are some examples:
     * <pre>
     * array(
     *     array('username', 'required'),
     *     array('username', 'length', 'min'=>3, 'max'=>12),
     *     array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
     *     array('password', 'authenticate', 'on'=>'login'),
     * );
     * </pre>
     *
     * Note, in order to inherit rules defined in the parent class, a child class needs to
     * merge the parent rules with child rules using functions like array_merge().
     *
     * @return array validation rules to be applied when {@link validate()} is called.
     */
    public function rules()
    {
        return array();
    }

    /**
     * Returns the  of the current connection database name.Default set database name is 'kb'.
     * 
     * @return string
     */
    protected function db()
    {
        return 'weixin';
    }
    
    /**
     * 得到读数据库.
     * 
     * @return \Components\Db\Connection
     */
    public function getReadDb()
    {
        return \Components\Db\Connection::instance()->read($this->db());
    }
    
    /**
     * 得到写数据库.
     * 
     * @return \Components\Db\Connection
     */
    public function getWriteDb()
    {
        return \Components\Db\Connection::instance()->write($this->db());
    }

    /**
     * Finds a single record data with the specified condition.
     * 
     * @param mixed   $condition     Query condition.
     * @param string  $findField     For find fields.
     * @param boolean $isFormatField Format fields.
     *
     * @return array record data.
     */
    public function find($condition = array(), $findField = '*', $isFormatField = false)
    {
        if ($findField == "*" && $isFormatField && isset($this::$fields)) {
            $fieldHumpMap = $this->getFieldHumpMap();
            foreach ($fieldHumpMap as $key => $field) {
                $formatFields[] = $key . ' as ' . $field;
            }
            $findField = implode(",", $formatFields);
        }
        $data   = $this->getReadDb()->select($findField)->from($this->getTableName())->where($condition)->limit(1)->queryRow();
        return $data;
    }

    /**
     * Finds all record data with the specified condition.
     *
     * @param mixed   $condition     Query condition.
     * @param string  $findField     For select fields.
     * @param string  $order         Order string.
     * @param boolean $isFormatField Format fields.
     *
     * @return array record data
     */
    public function findAll($condition = array(), $findField = '*', $order = null, $isFormatField = false)
    {
        if ($findField == "*" && $isFormatField && isset($this::$fields)) {
            $fieldHumpMap = $this->getFieldHumpMap();
            foreach ($fieldHumpMap as $key => $field) {
                $formatFields[] = $key . ' as ' . $field;
            }
            $findField = implode(",", $formatFields);
        }

        $finder     = $this->getReadDb()
            ->select($findField)
            ->from($this->getTableName());

        if ($condition) {
            $finder->where($condition);
        }
        if ($order) {
            $finder->order($order);
        }

        // 最大只允许一次获取500条数据.
        $finder->limit(0, 500);

        $data = $finder->queryAll();
        return $data;
    }

    /**
     * Finds all record data as the map with the specified condition.
     *
     * @param mixed   $condition     Query condition.
     * @param string  $findField     For select fields.
     * @param string  $order         Order string.
     * @param boolean $isFormatField Format fields.
     *
     * @return array record data
     */
    public function findAllAsMap($condition = array(), $findField = '*', $order = null, $isFormatField = false)
    {
        $data   = $this->findAll($condition, $findField, $order, $isFormatField);
        $map    = array();
        $keyCol = $this->primaryKey();
        
        if (!$data) { return $map; }

        if ($isFormatField) {
            $keyCol = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $keyCol))));
        }
        foreach ($data as $one) {
            $map[ $one[ $keyCol ] ] = $one;
        }

        return $map;
    }

    /**
     * Finds more record data with the specified condition(support page).
     * 
     * @param mixed   $condition     Query condition.
     * @param integer $limit         Item limit number.
     * @param integer $offset        Item offset number.
     * @param string  $findField     For select fields.
     * @param string  $order         Order string.
     * @param boolean $isFormatField Format fields.
     *
     * @return mixed record data
     */
    public function findPage($condition = array(), $limit = 20, $offset = 0, $findField = '*', $order = null, $isFormatField = false)
    {

        if ($findField == "*" && $isFormatField && isset($this::$fields)) {
            $fieldHumpMap = $this->getFieldHumpMap();
            foreach ($fieldHumpMap as $key => $field) {
                $formatFields[] = $key . ' as ' . $field;
            }
            $findField = implode(",", $formatFields);
        }

        $finder     = $this->getReadDb()
            ->select($findField)
            ->from($this->getTableName());

        if ($condition) {
            $finder->where($condition);
        }
        if ($order) {
            $finder->order($order);
        }

        if ($limit > 500) {
            // 最大只允许一次获取500条数据.
            $limit = 500;
        }

        $finder->limit($offset, $limit);
        $data   = $finder->queryAll();

        return $data;
    }

    /**
     * Finds all record data total number with the specified condition.
     * 
     * @param mixed $condition Query condition.
     *
     * @return integer
     */
    public function findCount($condition = array())
    {
        return $this->getReadDb()->count($this->getTableName(), $condition, 1);
    }

    /**
     * Finds a single record with the specified primary key.
     *
     * @param integer $id            Primary key id.
     * @param string  $findField     Select field name string, e.g. 'f1,f2', default '*'.
     * @param boolean $isFormatField Format fields.
     *
     * @return resource
     */
    public function findByPk($id, $findField = '*', $isFormatField = false)
    {
        if ($findField == "*" && $isFormatField && isset($this::$fields)) {
            $fieldHumpMap = $this->getFieldHumpMap();
            foreach ($fieldHumpMap as $key => $field) {
                $formatFields[] = $key . ' as ' . $field;
            }
            $findField = implode(",", $formatFields);
        }

        $data   = $this->getReadDb()
            ->select($findField)
            ->from($this->getTableName())
            ->where(array($this->primaryKey() => $id ))
            ->queryRow();
        return $data;
    }

    /**
     * Update the data by primary key.
     *
     * @param integer $id            User id.
     * @param mixed   $updateDb      Update user data array('column_name'=>'value').
     * @param boolean $runValidation 是否对数据进行验证，默认为true, 前天是rule()方法中已配置有检测方法.
     * @param boolean $isFormatField Format fields.
     * 
     * @return boolean 是否更新成功.
     */
    public function updateByPk($id, $updateDb = array(), $runValidation = true, $isFormatField = false)
    {
        if (!$id || !$updateDb) {
            return false;
        }
        $this->isNewRecord_ = false;
        $this->scenario_    = 'update';

        if ($this->beforeUpdate()) {
            // 装载对象数据.
            foreach ($updateDb as $k => $v) {
                $this->$k = $v;
            }

            if ($isFormatField) {
                // 将驼峰结构根据fields属性转换成下划线.
                $fieldHumpMap = $this->getFieldHumpMap(true);
                foreach ($updateDb as $key => $v) {
                    $formatAttributes[$fieldHumpMap[$key]] = $v;
                }
                $updateDb = $formatAttributes;
            }

            $pkName             = $this->primaryKey();
            $this->$pkName      = $id;

            if ($runValidation && !$this->validate()) {
                return false;
            }

            $this->getWriteDb()->update(
                $this->getTableName(),
                $updateDb,
                array($this->primaryKey() => $id)
            );

            $this->afterUpdate();

            return true;
        }

        return false;
    }

    /**
     * Update the data by other condition.
     *
     * @param mixed   $condition     Update condition array.
     * @param mixed   $updateDb      Update user data array('column_name'=>'value').
     * @param boolean $isFormatField Format fields.
     * 
     * @return integer
     */
    public function update($condition = array(), $updateDb = array(), $isFormatField = false)
    {
        $this->isNewRecord_ = false;
        $this->scenario_    = 'update';

        if ($this->beforeUpdate()) {
            if ($isFormatField) {
                // 将驼峰结构根据fields属性转换成下划线.
                $fieldHumpMap = $this->getFieldHumpMap(true);
                foreach ($updateDb as $key => $v) {
                    $formatAttributes[$fieldHumpMap[$key]] = $v;
                }
                $updateDb = $formatAttributes;
            }

            $result = $this->getWriteDb()->update(
                $this->getTableName(),
                $updateDb,
                $condition
            );

            $this->afterUpdate();

            return $result;
        } else {
            return 0;
        }
    }

    /**
     * Save the current model data.
     *
     * @param array   $attributes    保存数组.
     * @param boolean $isFormatField Format fields.
     * @param boolean $runValidation 是否对数据进行验证，默认为true, 前天是rule()方法中已配置有检测方法.
     *
     * @return mixed
     */
    public function save($attributes = array(), $isFormatField = false, $runValidation = true)
    {
        $this->isNewRecord_ = true;
        $this->scenario_    = 'insert';

        if ($this->beforeSave()) {
            // 装载对象数据.
            if ($attributes !== array()) {
                foreach ($attributes as $k => $v) {
                    $this->$k = $v;
                }
            } else {
                $attributes = $this->fieldProperties;
            }

            if ($runValidation && !$this->validate()) {
                return false;
            }

            if ($isFormatField) {
                // 将驼峰结构根据fields属性转换成下划线.
                if (isset($this::$fields)) {
                    $fieldHumpMap = $this->getFieldHumpMap(true);
                    foreach ($attributes as $key => $v) {
                        $formatAttributes[$fieldHumpMap[$key]] = $v;
                    }
                    $attributes = $formatAttributes;
                }
            }

            $result = $this->getWriteDb()->insert($this->getTableName(), $attributes);

            $this->afterSave();

            return $result;
        }

        return false;
    }

    /**
     * Finds a single record data with format.
     * 
     * @param mixed  $condition Query condition.
     * @param string $findField For find fields.
     *
     * @return array record data.
     */
    public function findF($condition = array(), $findField = '*')
    {
        return $this->find($condition, $findField, true);
    }

    /**
     * Finds all record data with format. 
     *
     * @param mixed  $condition Query condition.
     * @param string $findField For select fields.
     * @param string $order     Order string.
     *
     * @return array record data
     */
    public function findAllF($condition = array(), $findField = '*', $order = null)
    {
        return $this->findAll($condition, $findField, $order, true);
    }

    /**
     * Finds all record data as findAllAsMap with format.
     *
     * @param mixed  $condition Query condition.
     * @param string $findField For select fields.
     * @param string $order     Order string.
     *
     * @return array record data
     */
    public function findAllAsMapF($condition = array(), $findField = '*', $order = null)
    {
        return $this->findAllAsMap($condition, $findField, $order, true);
    }

    /**
     * Finds more record data with format. 
     * 
     * @param mixed   $condition Query condition.
     * @param integer $limit     Item limit number.
     * @param integer $offset    Item offset number.
     * @param string  $findField For select fields.
     * @param string  $order     Order string.
     *
     * @return mixed record data
     */
    public function findPageF($condition = array(), $limit = 20, $offset = 0, $findField = '*', $order = null)
    {
        return $this->findPage($condition, $limit, $offset, $findField, $order, true);
    }

    /**
     * Finds a single record with the specified primary key and the format.
     *
     * @param integer $id        Primary key id.
     * @param string  $findField Select field name string, e.g. 'f1,f2', default '*'.
     *
     * @return resource
     */
    public function findByPkF($id, $findField = '*')
    {
        return $this->findByPk($id, $findField, true);
    }

    /**
     * Delete one data by primary key.
     *
     * @param integer $id The primary key id.
     *
     * @return mixed
     */
    public function deleteByPk($id)
    {
        $this->scenario_    = 'delete';

        if ($this->beforeDelete()) {
            $primaryKey = $this->primaryKey();
            if (!$id && $this->$primaryKey) {
                $id = $this->$primaryKey;
            }

            $result = $this->deleteAll(array($primaryKey => $id));
            $this->afterDelete();
            return $result;
        }
    }

    /**
     * Delete all data by conditions.
     *
     * @param mixed $condition The delete condition array.
     *
     * @return mixed
     */
    public function deleteAll($condition = array())
    {
        $this->scenario_    = 'delete';

        return $this->getWriteDb()->delete($this->getTableName(), $condition);
    }

    /**
     * 事务开始.
     *
     * @param boolean $global If rollback the global transaction.
     *
     * @return void
     */
    public function beginTransaction($global = false)
    {
        $this->getWriteDb()->beginTransaction($global);
    }

    /**
     * 事务提交.
     *
     * @param boolean $global If rollback the global transaction.
     *
     * @return void
     */
    public function commit($global = false)
    {
        $this->getWriteDb()->commit($global);
    }

    /**
     * 事务回滚.
     *
     * @param boolean $global If rollback the global transaction.
     *
     * @return void
     */
    public function rollback($global = false)
    {
        $this->getWriteDb()->rollback($global);
    }

}

