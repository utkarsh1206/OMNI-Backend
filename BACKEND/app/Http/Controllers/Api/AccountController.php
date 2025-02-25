<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AccountResource;
use App\Repositories\AccountRepository;

class AccountController extends Controller
{


  private $accountRepository;
  public function __construct(AccountRepository $accountRepository)
  {
      $this->accountRepository = $accountRepository;
  }

  public function index(Request $request)
  {
    return $this->accountRepository->list($request);
  }

  public function store(AccountRequest $request)
  {
    return $this->accountRepository->store($request);
  }

  public function show(Account $account)
  {
    return $this->accountRepository->show($account);
  }

  public function update(AccountRequest $request, string $id)
  {
    return $this->accountRepository->update($request,$id);
  }

  public function destroy(string $id)
  {
    return $this->accountRepository->delete($id);
  }
}
