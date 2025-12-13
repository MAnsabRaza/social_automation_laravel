<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {

    // Create proxies table
    Schema::create('proxies', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->date('current_date');
      $table->enum('proxy_type', ['http', 'https', 'socks4', 'socks5']);
      $table->string('proxy_host', 255);
      $table->string('proxy_port');
      $table->string('proxy_username', 100)->nullable();
      $table->string('proxy_password', 255)->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamp('last_used')->nullable();
      $table->timestamps();

      // Foreign key
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });


    Schema::create('social_accounts', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->date('current_date');
      $table->string('platform', 255);
      $table->string('account_username', 150);
      $table->string('account_email', 150)->nullable();
      $table->string('account_password', 255);
      $table->string('account_phone', 50)->nullable();
      $table->text('cookies')->nullable();
      $table->text('auth_token')->nullable();
      $table->text('session_data')->nullable();
      $table->unsignedBigInteger('proxy_id')->nullable();
      $table->string('status');
      $table->timestamp('last_login')->nullable();
      $table->integer('warmup_level')->default(0);
      $table->integer('daily_actions_count')->default(0);
      $table->timestamps();
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('proxy_id')->references('id')->on('proxies')->onDelete('set null');
    });
    // Create captcha_settings table
    Schema::create('captcha_settings', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->enum('service_name', ['2captcha', 'anticaptcha', 'deathbycaptcha']);
      $table->string('api_key', 255);
      $table->boolean('status')->default(true);
      $table->timestamps();

      // Foreign key
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    // Create post_content table
    Schema::create('post_content', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      
      $table->unsignedBigInteger('account_id');
      $table->string('title', 255)->nullable();
      $table->text('content');
      $table->text('media_urls')->nullable();
      $table->text('hashtags')->nullable();
      $table->boolean('spintax_enabled')->default(false);
      $table->timestamps();

      // Foreign key
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('account_id')->references('id')->on('social_accounts')->onDelete('cascade');
    });

    // Create tasks table
    Schema::create('tasks', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->unsignedBigInteger('account_id');
      $table->enum('task_type', ['post', 'comment', 'like', 'follow', 'unfollow', 'share', 'review']);
      $table->string('target_url', 500)->nullable();
      $table->text('content')->nullable();
      $table->text('hashtags')->nullable();
      $table->longText('media_urls')->nullable();
      $table->timestamp('scheduled_at')->nullable();
      $table->timestamp('executed_at')->nullable();
      $table->timestamps();
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('account_id')->references('id')->on('social_accounts')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {

    Schema::dropIfExists('proxies');
    Schema::dropIfExists('social_accounts');
    Schema::dropIfExists('captcha_settings');
    Schema::dropIfExists('post_content');
    Schema::dropIfExists('tasks');

  }
};
