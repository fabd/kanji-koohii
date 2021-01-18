<?php

class DeleteAccountConfirmComponent extends sfComponent
{
  public function execute($request)
  {
    $user = $this->getUser();

    if ($request->getMethod() != sfRequest::POST)
    {
      $formdata = [
        'email' => '',
        'confirm_text' => '',
        'password' => '',
      ];

      $request->getParameterHolder()->add($formdata);
    }
    else
    {
      $validator = new coreValidator($this->getActionName());

      if ($validator->validate($request->getParameterHolder()->getAll()))
      {
        $inputs = [
          'email' => trim($request->getParameter('email')),
          'confirm_text' => trim($request->getParameter('confirm_text', '')),
          'password' => trim($request->getParameter('password')),
        ];

        $userDetails = $user->getUserDetails();

        // hmm this might be an issue with the legacy code

        $isValidEmail = strtolower($inputs['email']) === strtolower($userDetails['email']);
        $isValidPassword = $user->getSaltyHashedPassword($inputs['password']) === $userDetails['password'];
        $isValidPhrase = $inputs['confirm_text'] === 'delete my account';

        if (!$isValidEmail)
        {
          $request->setError('email', 'Email is incorrect. Make sure you type it correctly');
        }
        if (!$isValidPassword)
        {
          $request->setError('password', 'Password is incorrect. Did you type it correctly?');
        }
        if (!$isValidPhrase)
        {
          $request->setError('confirm_text', 'Please type exact phrase in lowercase letters');
        }

        if (
          $isValidEmail
          && $isValidPhrase
          && $isValidPassword
        ) {
          if (1 /* UsersPeer::deleteUser($user->getUserId()) */ )
          {
            return 'YES';
//            $this->redirect('account/index');
          }

          $request->setError('db', 'Oops, the delete operation failed. Please try again in a minute.');
        }
      }
    }
  }
}
