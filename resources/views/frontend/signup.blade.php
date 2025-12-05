@extends('layouts.main')

@section('content')

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <img src="" alt="" class="mx-auto h-10 w-auto" />
    <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">
      Create your account
    </h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">

    <!-- SIGNUP FORM — JS handles all submission -->
    <form id="signupForm" class="space-y-6">

      <!-- NAME -->
      <div>
        <label class="block text-sm/6 font-medium text-gray-900">Username</label>
        <div class="mt-2">
          <input id="name" type="text" required autocomplete="name"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900
                   outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400
                   focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 
                   sm:text-sm/6" />
        </div>
      </div>

      <!-- EMAIL -->
      <div>
        <label class="block text-sm/6 font-medium text-gray-900">Email address</label>
        <div class="mt-2">
          <input id="email" type="email" required autocomplete="email"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900
                   outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400
                   focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 
                   sm:text-sm/6" />
        </div>
      </div>

      <!-- PASSWORD -->
      <div>
        <label class="block text-sm/6 font-medium text-gray-900">Password</label>
        <div class="mt-2">
          <input id="password" type="password" required autocomplete="new-password"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900
                   outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400
                   focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 
                   sm:text-sm/6" />
        </div>
      </div>

      <!-- SUBMIT -->
      <div>
        <button type="submit"
          class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 
                 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 
                 focus-visible:outline-2 focus-visible:outline-offset-2 
                 focus-visible:outline-indigo-600">
          Create Account
        </button>
      </div>

    </form>

    <!-- SWITCH TO LOGIN -->
    <p class="mt-10 text-center text-sm/6 text-gray-500">
      Already have an account?
      <a href="/sign-in" class="font-semibold text-indigo-600 hover:text-indigo-500">Login</a>
    </p>
  </div>
</div>

@endsection
