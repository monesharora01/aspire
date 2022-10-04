<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    /** Loan to loan repayments relation */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
