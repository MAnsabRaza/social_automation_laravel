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

    // Create proxy_api_settings table
    Schema::create('proxy_api_settings', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->string('api_name', 100)->nullable();
      $table->string('api_url', 255)->nullable();
      $table->string('api_key', 255)->nullable();
      $table->boolean('rotation_enabled')->default(false);
      $table->timestamps();

      // Foreign key
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    // Create comment_templates table
    Schema::create('comment_templates', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->text('template_text');
      $table->boolean('spintax_enabled')->default(true);
      $table->string('category', 100)->nullable();
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
    //Account Group
    Schema::create('account_groups', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->string('group_name', 150);
      $table->text('description')->nullable();
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    // Activity Logs
    Schema::create('activity_logs', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('account_id');
      $table->string('action_type', 100);
      $table->text('action_detail')->nullable();
      $table->enum('status', ['success', 'failed'])->default('success');
      $table->string('ip_address', 50)->nullable();
      $table->text('user_agent')->nullable();
      $table->timestamps();

      $table->foreign('account_id')->references('id')->on('social_accounts')->onDelete('cascade');
    });
    // Account Group Mapping
    Schema::create('account_group_mapping', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('group_id');
      $table->unsignedBigInteger('account_id');
      $table->timestamps();

      $table->foreign('group_id')->references('id')->on('account_groups')->onDelete('cascade');
      $table->foreign('account_id')->references('id')->on('social_accounts')->onDelete('cascade');

      $table->unique(['group_id', 'account_id'], 'unique_account_group');
    });
    // Create system_settings table
    Schema::create('system_settings', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->string('setting_key', 100)->unique();
      $table->text('setting_value')->nullable();
      $table->string('setting_type', 50)->nullable();
      $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    });
    // Create warmup_settings table
    Schema::create('warmup_settings', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->integer('day_number');
      $table->integer('max_posts')->default(0);
      $table->integer('max_likes')->default(0);
      $table->integer('max_comments')->default(0);
      $table->integer('max_follows')->default(0);
      $table->integer('max_unfollows')->default(0);
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->unique(['user_id', 'day_number'], 'unique_day');
    });

    // Create tasks table
    Schema::create('tasks', function (Blueprint $table) {
      $table->id();
      $table->date('current_date');
      $table->unsignedBigInteger('user_id');
      $table->unsignedBigInteger('account_id');
      $table->enum('task_type', ['post', 'comment', 'like', 'follow', 'unfollow', 'share', 'review']);
      $table->text('task_content')->nullable();
      $table->string('target_url', 500)->nullable();
      $table->timestamp('scheduled_at')->nullable();
      $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
      $table->integer('priority')->default(0);
      $table->integer('retry_count')->default(0);
      $table->text('error_message')->nullable();
      $table->timestamp('executed_at')->nullable();
      $table->timestamps();

      // Foreign keys
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
    Schema::dropIfExists('proxy_api_settings');
    Schema::dropIfExists('comment_templates');
    Schema::dropIfExists('post_content');
    Schema::dropIfExists('account_groups');
    Schema::dropIfExists('activity_logs');
    Schema::dropIfExists('account_group_mapping');
    Schema::dropIfExists('system_settings');
    Schema::dropIfExists('warmup_settings');
    Schema::dropIfExists('tasks');

  }
};
