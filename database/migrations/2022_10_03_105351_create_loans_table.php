<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->bigInteger('user_id')->unsigned();
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('term')->default(1);
            $table->tinyInteger('state')->default(0)->comment('0:Pending,1:Approved,2:Rejected');
            $table->tinyInteger('status')->default(0)->comment('0:Not Paid,1:Paid');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
