<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faq', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('cate_id')->unsigned()->comment('分類ID');
            $table->dateTime('created_at')->comment('建立資料時間');
            $table->dateTime('updated_at')->comment('最後編輯資料時間');
            $table->text('title')->comment('問題');
            $table->text('answer')->comment('回答');
            $table->bigInteger('order')->unsigned()->comment('排序');
            $table->tinyInteger('enable')->unsigned()->comment('0:停用 1:啟用');
            $table->dateTime('delete')->nullable()->comment('刪除時間');
            $table->integer('creat_admin_id')->unsigned()->nullable()->comment('建立資料管理員ID');
            $table->integer('update_admin_id')->unsigned()->nullable()->comment('最夠更新資料管理員ID');
        });

        Schema::create('faq_lang', function (Blueprint $table) {
            $table->bigInteger('faq_id')->unsigned()->comment('faq_id');
            $table->string('lang',3)->comment('語言');
            $table->dateTime('created_at')->comment('建立資料時間');
            $table->dateTime('updated_at')->comment('最後編輯資料時間');
            $table->text('title')->comment('問題');
            $table->text('answer')->comment('回答');
            $table->integer('creat_admin_id')->unsigned()->nullable()->comment('建立資料管理員ID');
            $table->integer('update_admin_id')->unsigned()->nullable()->comment('最夠更新資料管理員ID');
            $table->primary(['faq_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faq');
        Schema::dropIfExists('faq_lang');
    }
}
