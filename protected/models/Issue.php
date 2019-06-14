<?php

/**
 * This is the model class for table "api_token".
 *
 * The followings are the available columns in table 'api_token':
 * @property string $id
 * @property integer $user_id
 * @property string $token
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Issue extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ApiToken the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'issue';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, type', 'required'),
            array('user_id, type, created_at, updated_at', 'numerical', 'integerOnly'=>true),
            array('file_name, extension', 'length', 'max'=>255),
            array('file_name', 'file', 'types'=>'jpg, jpeg, png', 'maxSize'=>1024*1024*10, 'tooLarge'=>'File size cannot exceed 10 MB.'),
            array('extension, file_name', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, created_at, updated_at', 'safe', 'on'=>'search'),
        );
    }
	
	public function getFileName() {
        return "$this->id.$this->extension";
    }

    public function getFilePath() {
        return Yii::app()->basePath."/../issue/$this->id.$this->extension";
    }

    public function getFileUrl() {
        return "http://52.221.250.196/".Yii::app()->baseUrl."/issue/".$this->getFileName();
    }
	
	public function beforeSave() {
		if($this->isNewRecord)
			$this->created_at = time();
		$this->updated_at = time();
		return parent::beforeSave();
	}

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'token' => 'Token',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id,true);
        $criteria->compare('user_id',$this->user_id);
        $criteria->compare('token',$this->token,true);
        $criteria->compare('status',$this->status);
        $criteria->compare('created_at',$this->created_at);
        $criteria->compare('updated_at',$this->updated_at);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
}
?>