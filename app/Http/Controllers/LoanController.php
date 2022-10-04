<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Support\Str;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class LoanController extends Controller
{
    public function index()
    {
        $loans = auth()->user()->loans;

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    public function show($uuid)
    {
        $loan = auth()->user()->loans()->where(['uuid' => $uuid])->first();

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'loan not found '
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $loan->toArray()
        ], 400);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|gt:0',
            'term' => 'required|numeric|gt:0'
        ]);

        $loan = new Loan();
        $loan->uuid = Str::uuid()->toString();
        $loan->amount = $request->amount;
        $loan->term = $request->term;


        if (auth()->user()->loans()->save($loan)) {
            $savedLoan = $loan->toArray();
            $loan_id = $savedLoan['id'];
            $term = $request->term;
            $amount = $request->amount;
            $dividedAmount = $amount / $term;
            for ($i = 1; $i <= $term; $i++) {
                $repayment_date = Carbon::now();
                $repayment_date = $i == 0 ? $repayment_date : $repayment_date->addDays(7 * $i);
                $loanRp = new LoanRepayment();
                $loanRp->loan_id = $loan_id;
                $loanRp->amount_to_pay = $dividedAmount;
                $loanRp->repayment_date = $repayment_date;
                auth()->user()->loan_repayments()->save($loanRp);
            }
            return response()->json([
                'success' => true,
                'data' => $savedLoan
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'loan not added'
            ], 500);
        }
    }

    public function update(Request $request, $uuid)
    {
        $loan = auth()->user()->loans()->where(['uuid' => $uuid])->first();

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'loan not found'
            ], 400);
        }

        $updated = $loan->fill($request->all())->save();

        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'loan can not be updated'
            ], 500);
    }

    public function destroy($uuid)
    {
        $loan = auth()->user()->loans()->where(['uuid' => $uuid])->first();

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'loan not found'
            ], 400);
        }

        if ($loan->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'loan can not be deleted'
            ], 500);
        }
    }

    public function approve(Request $request, $uuid)
    {

        if (!auth()->user()->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access. Only Admin'
            ], 401);
        }

        $loan = Loan::where(['uuid' => $uuid])->first();

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'loan not found'
            ], 400);
        }

        $updated = false;
        if ($loan->state == 0) {
            $loan->state = 1;
            $loan->approved_at = Carbon::now();
            $updated = $loan->save();
        }

        if ($updated)
            return response()->json([
                'success' => true,
                'message' => 'loan state approved'
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'loan state can not be approved or already approved'
            ], 500);
    }

    public function repayment(Request $request, $id)
    {

        if (!auth()->user()->hasRole('customer')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access. Only Customer'
            ], 401);
        }

        $loanRp = auth()->user()->loan_repayments()->where(['id' => $id])->first();

        if (!$loanRp) {
            return response()->json([
                'success' => false,
                'message' => 'loan repayment not found'
            ], 400);
        }

        if ($loanRp->loan->state != 1) {
            return response()->json([
                'success' => false,
                'message' => 'loan is not yet approved by admin'
            ], 400);
        }

        $this->validate($request, [
            'amount' => 'required|numeric|gte:' . $loanRp->amount_to_pay,
        ]);


        $updated = false;
        if ($loanRp->state == 0) {
            $loanRp->state = 1;
            $loanRp->status = 1;
            $loanRp->paid_at = Carbon::now();
            $updated = $loanRp->save();
        }

        //Check if all repayments paid
        $allRepayments = $loanRp->loan->repayments;
        $allPaid = true;
        foreach ($allRepayments as $k => $v) {
            if ($v->state == 0) {
                $allPaid = false;
            }
        }

        //If all paid mark loan as paid
        if ($allPaid) {
            $loan = $loanRp->loan;
            $loan->status = 1;
            $loan->save();
        }

        if ($updated)
            return response()->json([
                'success' => true,
                'message' => 'loan repayment paid successfully'
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'loan repayment failed or already paid'
            ], 500);
    }
}
