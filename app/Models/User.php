<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject 
{
   /** @use HasFactory<\Database\Factories\UserFactory> */
   use HasFactory, Notifiable;

   protected $primaryKey = 'user_id';

   /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
   protected $fillable = [
       'user_name',
       'user_email',
       'user_password',
       'user_role',
       'active_token'
   ];

   /**
    * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
   protected $hidden = [
       'user_password',
       'remember_token',
   ];

   /**
    * Get the attributes that should be cast.
    *
    * @return array<string, string>
    */
   protected function casts(): array
   {
       return [
           'user_email_verified_at' => 'datetime',
           'user_password' => 'hashed',
           'user_role' => 'string',
       ];
   }

   /**
    * Get the identifier that will be stored in the subject claim of the JWT.
    *
    * @return mixed
    */
   public function getJWTIdentifier()
   {
       return $this->getKey();
   }

   /**
    * Return a key value array, containing any custom claims to be added to the JWT.
    *
    * @return array
    */
   public function getJWTCustomClaims()
   {
       return [];
   }

   /**
    * Get the name of the unique identifier for the user.
    */
   public function getAuthIdentifierName()
   {
       return 'user_id';
   }

   /**
    * Get the password for the user.
    */
   public function getAuthPassword()
   {
       return $this->user_password;
   }

   /**
    * Get the email for the user.
    */
   public function getEmailAttribute()
   {
       return $this->user_email;
   }

   /**
    * Get the password for the user.
    */
   public function getPasswordAttribute()
   {
       return $this->user_password;
   }

   public function username()
   {
       return 'user_email';
   }

   public function reservations()
   {
       return $this->hasMany(Reservation::class, 'user_id', 'user_id');
   }
}