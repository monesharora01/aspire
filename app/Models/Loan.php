<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;


    /** Loan to loan repayments relation */
    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
