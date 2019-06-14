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
class ApiToken extends CActiveRecord
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
        return 'api_token';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, status, created_at, updated_at', 'numerical', 'integerOnly'=>true),
            array('token', 'length', 'max'=>255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, token, status, created_at, updated_at', 'safe', 'on'=>'search'),
        );
    }
	
	public static function createTokenForUser($user) {
        $token = sha1($user->email.time());

		ApiToken::expireTokensForUserId($user->id);

		$api_token = new ApiToken;
		$api_token->user_id = $user->id;
		$api_token->status=self::STATUS_ACTIVE;
		$api_token->token = $token;
		$api_token->save();

		return $token;
	}
	
	public static function expireTokensForUserId($user_id) {
		ApiToken::model()->updateAll(array('status'=>self::STATUS_INACTIVE), "user_id=:user_id", array("user_id"=> $user_id));
	}
	
	public function scopes()
    {
        return array(
            'active'=>array(
                'condition'=>'status='.self::STATUS_ACTIVE,
            ),
		);
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