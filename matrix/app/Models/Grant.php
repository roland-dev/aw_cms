<?php

namespace Matrix\Models;

use Illuminate\Database\QueryException;

class Grant extends BaseModel
{
    protected $fillable  = ['user_id','permission_code','active'];
    //
    public function getOneGrantedList(int $userId)
    {
        $grantedList = self::where('user_id', $userId)->where('active', 1)->get();

        return empty($grantedList) ? [] : $grantedList->toArray();
    }


    //coded by Jiangzd
    public function getOneList(int $userId){
       $permissionCodeList = self::where('user_id', $userId)->get(); 
       return empty($permissionCodeList) ? [] : $permissionCodeList->toArray();
    }

   //coded by Jiangzd
   public function addUserPermission(int $userId, array $addUserPermission)
   {
     $addPermissionList = [];
	 foreach($addUserPermission as $addGrantedPermission)
	 {
		 $addPermissionList[] = [
			 'user_id'=>$userId,
			 'permission_code'=>$addGrantedPermission,
			 'active'=>1,
			 'created_at'=>date("Y-m-d H:i:s"),
			 'updated_at'=>date("Y-m-d H:i:s")
		 ];
	 }

     try{
        if(empty($addPermissionList)){
           return;
        }
		self::insert($addPermissionList);
     }catch(QueryException $e){
         $e->getMessage();
     }
   }

   //coded by Jiangzd
   public function removeGrantedList(int $userId, array $removeGrantedList)
   {
       try{
           if(empty($removeGrantedList)){
              return;
           }
		   self::whereIn('permission_code',$removeGrantedList)->where('user_id',$userId)->update(['active'=>0]);
       }catch(QueryException $e){
           $e->getMessage(); 
       }
   }

   //coded by Jiangzd
   public function reGrantedList(int $userId, array $reGrantedList)
   {
       try{
          if(empty($reGrantedList)){
             return;
          }
          self::whereIn('permission_code', $reGrantedList)->where('user_id',$userId)->update(['active'=>1]);
       }catch(QueryException $e){
          $e->getMessage(); 
       }
   }

    public function getGrantedList(array $userIdList, array $condition = [])
    {
        $baseCondition = ['active', '=', 1];
        $condition[] = $baseCondition;
        $grantedList = self::where($condition)->whereIn('user_id', $userIdList)->get();

        return empty($grantedList) ? [] : $grantedList->toArray();
    }
}

