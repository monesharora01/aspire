<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('loan_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->decimal('amount_to_pay', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->tinyInteger('state')->default(0)->comment('0:Pending,1:Approved,2:Rejected');
            $table->tinyInteger('status')->default(0)->comment('0:Not Paid,1:Paid');
            $table->dateTime('repayment_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayments');
    }
}
