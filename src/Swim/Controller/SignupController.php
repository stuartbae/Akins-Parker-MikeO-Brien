<?php

namespace Swim\Controller;

use Swim\Entity\User;
use Swim\Entity\Student;
use Swim\Entity\StudentCollection;
use Swim\Entity\Address;
use Swim\Entity\Group;
use Swim\Entity\Registration;
use Swim\Entity\HostRegistration;
use Swim\Entity\GuestRegistration;
use Swim\Entity\StudentPreference;
use Swim\Entity\SchedulePref;
use Swim\Form\Type\UserType;
use Swim\Form\Type\StudentType;
use Swim\Form\Type\PoolType;
use Swim\Form\Type\AddressType;
use Swim\Form\Type\PaymentType;

use Swim\Form\Type\UserSignupType;
use Swim\Form\Type\StudentSignupType;
use Swim\Form\Type\GroupSignupType;
use Swim\Form\Type\GroupCodeType;
use Swim\Form\Type\GroupDetailSignupType;
use Swim\Form\Type\StudentScheduleType;
use Swim\Form\Type\OpenGroupType;

use Swim\Form\Type\HostRegistrationType;
use Swim\Form\Type\GuestRegistrationType;
use Swim\Form\Type\StudentPreferenceType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class SignupController
{

    protected $modes;

    public function __construct($modes=null)
    {
        $this->modes = $modes ? $modes : array('host', 'guest', 'place');
    }
    public function indexAction(Request $request, Application $app)
    {
      $app['session']->clear();
      $data = array();
      return $app['twig']->render('index.signup.html.twig', $data);
    }

    public function resetAction(Request $request, Application $app)
    {
        $app['session']->clear();
        $data = array();
        return $app['twig']->render('index.signup.html.twig', $data);
    }



    /**
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function guestSignupAction(Request $request, Application $app, $group=null)
    {

        $group = $request->attributes->get('group');
        $form = $app['form.factory']->create(new GroupCodeType());

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $group = $form->getData();
                $group = $app['repository.group']->findByCode($group->getCode());
                if($group) {
                    $redirect = $app['url_generator']->generate('signup_user',
                        array('mode' => 'guest', 'group' => $group->getId()));
                    return $app->redirect($redirect);
                } else {
                    $message = "Please try again with a valid Group Code.";
                    $app['session']->getFlashBag()->add('danger', $message);
                }

            }
        }

        $data = array(
                    'form' => $form->createView(),
                    'mode' => 'guest',
                    );
        return $app['twig']->render('form.signup.groupcode.html.twig', $data);
    }


    public function userSignupAction(Request $request, Application $app, $mode, $group=null)
    {
        // $mode = $request->query->get('mode');
        if( false == in_array($mode, $this->modes) ) {
            $redirect = $app['url_generator']->generate('signup');
            return $app->redirect($redirect);
        }

        $group = $group ? $request->attributes->get('group') : new Group();

        $type = new UserSignupType();
        $form = $app['form.factory']->create($type, $this->unfreezeUserSignup($app));

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form_data = $form->getData();
                $user = $form_data['user'];

                if (false !== $app['repository.user']->findByUsername($user->getEmail())){
                    $message = 'The email address has already been registered!';
                    $app['session']->getFlashBag()->add('danger', $message);

                    $redirect = $app['url_generator']->generate('signup_user', array('mode' => $mode, 'group' => $group->getId()));
                    return $app->redirect($redirect);
                }

                    $this->freezeUserSignup($app, $form_data);
                // try {
                //     $user->setRole('ROLE_USER');
                //     $user_id = $app['repository.user']->save($user);
                //     $user->setUserId($user_id);
                //     $user->getAddress()->setUserId($user_id);
                //     $app['repository.address']->save($user->getAddress());

                //     $app['session']->set('REG_USER', serialize($user));

                // } catch (Exception $e) {

                //     $message = "User registration has been failed. Please try again or contact us.";
                //     $app['session']->getFlashBag()->add('danger', $message);

                //     $redirect = $app['url_generator']->generate('signup_user', array('mode' => $mode, 'group' => $group->getId()));
                //     return $app->redirect($redirect);

                // }
                //check the email is already been registered
                $redirect = $app['url_generator']->generate('signup_student' , array('mode' => $mode, 'group' => $group->getId()));
                return $app->redirect($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'mode' => $mode,
            'group' => $group,
            );
        return $app['twig']->render('form.signup.user.html.twig', $data);
    }

    public function studentSignupAction(Request $request, Application $app, $mode, $group=null)
    {
        if( false == in_array($mode, $this->modes) ) {
            $redirect = $app['url_generator']->generate('signup');
            return $app->redirect($redirect);
        }
        $group = $group ? $request->attributes->get('group') : new Group();

        $user = $this->unfreezeUserSignup($app);
        if ( null == $user ) {
            $redirect = $app['url_generator']->generate('signup_user', array('mode' => $mode, $group->getId()));
            return $app->redirect($redirect);
        }
        // dump("user", $user);

        $type = new StudentSignupType($app);
        $new_signup = new StudentCollection();
        $new_signup->addStudent(new Student());
        $prev_signup = $this->unfreezeStudentSignup($app);
        $entity = $prev_signup ? $prev_signup : $new_signup;
        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $new_signup = $form->getData();
                // dump("students", $new_signup);
                //if this first submit add temp student_id for diff student next step
                $col_students = $new_signup->getStudents();
                if( null == $col_students->current()->getStudentId()) {
                    foreach ($col_students as $student) {
                        $student->setStudentId(uniqid());
                    }
                }
                $this->freezeStudentSignup($app, $new_signup);

                $url = ($mode == 'guest') ? 'signup_payment' : 'signup_pick_group';
                $redirect = $app['url_generator']->generate($url, array('mode' => $mode, 'group' => $group->getId()));
                return $app->redirect($redirect);
            }

        }

        $data = array(
            'form' => $form->createView(),
            'group' => $group,
            'mode' => $mode,
            );
        return $app['twig']->render('form.signup.student.html.twig', $data);
    }

    public function pickGroupAction(Request $request, Application $app, $mode, $group=null)
    {
        $group = $group ? $request->attributes->get('group') : new Group();

        $student_collection = $this->unfreezeStudentSignup($app);
        if ( null == $student_collection ) {
            $redirect = $app['url_generator']->generate('signup_student', array('mode' => $mode, 'group' => $group->getId()));
            return $app->redirect($redirect);
        }

        $students = $student_collection->getStudents()->getValues();

        $prev_pick = $this->unfreezeGroupSignup($app);
        $entity = $prev_pick ? $prev_pick : null;

        $rows = $app['repository.group']->findAllOpen(100);
        foreach ($rows as $key => $value) {
            // $open_groups[$value['group_id']] = date('D h:i a' ,$value['starts_at']);
            $open_groups[$value['group_id']] = $value['starts_at'];
            $label = date('m-d-Y', $value['starts_at']);
            $id = date('m_d', $value['starts_at']);
            $open_days [$id] = $label;
        }
        $open_days = array_unique($open_days);

        if ($request->isMethod('POST') === true) {

            if(count($students) != count(array_keys($_POST))) {
                $message = "Please select each student's prerferred schdules";
                $app['session']->getFlashBag()->add('danger', $message);

                $redirect = $app['url_generator']->generate('signup_pick_group', array('mode' => $mode, 'group' => $group->getId()));
                return $app->redirect($redirect);
            }

            foreach ($_POST as $key => $value) {
                $group_pick[$key] = array_unique($value);
            }

            // dump($group_pick);
            $this->freezeGroupSignup($app, $group_pick);

                if ($mode == 'host'){
                    $redirect = $app['url_generator']->generate('signup_group_detail', array('mode' => $mode, 'group' => $group->getId()));
                    return $app->redirect($redirect);

                } else {
                    $redirect = $app['url_generator']->generate('signup_payment', array('mode' => $mode, 'group' => $group->getId()));
                    return $app->redirect($redirect);
                }
        }


        $data = array(
            // 'forms' => $forms,
            'mode' => $mode,
            'group' => $group,
            'students' => $students,
            'open_groups' => $open_groups,
            'open_days' => $open_days,
            );
        // return $app['twig']->render('form.signup.group.html.twig', $data);
        return $app['twig']->render('form.signup.group.html.twig', $data);
    }

    public function groupDetailAction(Request $request, Application $app, $mode, $group=null)
    {
        // $app['session']->set('STUDENT_SIGNUP', null);exit();
        $group = $group ? $request->attributes->get('group') : new Group();
        $group_pick = $this->unfreezeGroupSignup($app);
        if ( null == $group ) {
            $redirect = $app['url_generator']->generate('signup_pick_group', array('mode' => $mode, 'group' => $group->getId()));
            return $app->redirect($redirect);
        }
        // dump($group);

        $prev = $this->unfreezeGroupDetailSignup($app);
        // dump($prev);
        $entity = $prev ? $prev : null;
        $type = new GroupDetailSignupType($app);
        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $group_detail = $form->getData();

                $pool_id = $app['repository.pool']->save($group_detail->pool);
                //save it to lesson table

                $this->freezeGroupDetailSignup($app, $group_detail);
                    $redirect = $app['url_generator']->generate('signup_payment', array('mode' => $mode, 'group' => $group->getId()));
                    return $app->redirect($redirect);
            }
        }

        $data = array('form' => $form->createView(),
            'mode' => 'host',
            'group' => $group,
            );
        return $app['twig']->render('form.signup.groupdetail.html.twig', $data);
    }

    public function paymentAction(Request $request, Application $app, $mode, $group=null)
    {
        $group = $group ? $request->attributes->get('group') : new Group();
        $group_pick = $this->unfreezeGroupSignup($app);
        if ( $mode != 'guest' && null == $group_pick ) {
            $redirect = $app['url_generator']->generate('signup_student_pick_group', array('mode' => $mode, 'group' => $group->getId()));
            return $app->redirect($redirect);
        }

        // $prev_payment = $this->unfreezePaymentSignup($app);
        // $entity = $prev_payment ? $prev_payment : null;
        $type = new PaymentType($app);
        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
                // dump($form);
            if ($form->isValid()) {
                $new_payment = $form->getData();
                // dump($new_payment);
                $this->freezePaymentSignup($app, $new_payment);

                $students = $this->unfreezeStudentSignup($app)->getStudents();
                $discount = 0;
                $deposit = $payment->fullpay ? 380 : 190;
                $total = ($deposit - $discount) * count($students);


                $redirect = $app['url_generator']->generate('signup_payment_confirm', array('mode' => $mode, 'group' => $group->getId()));
                return $app->redirect($redirect);
            }

        }

        $deposit = 190;
        $data = array(
            'form' => $form->createView(),
            'deposit' => $deposit,
            'quantity' => $this->unfreezeStudentSignup($app)->getStudents()->count(),
            'mode' => $mode,
            'group' => $group,
            );
        return $app['twig']->render('form.signup.payment.html.twig', $data);
    }

    public function confirmAction(Request $request, Application $app, $mode, $group=null)
    {
        $group = $group ? $request->attributes->get('group') : new Group();
        // $group_param = $request->attributes->get('group');
        // $app['session']->set(serialize($group_param), 'GROUP_PARAM');

        $user_col = $this->unfreezeUserSignup($app);
        $user = $user_col['user'];
        // $address = $this->unfreezeUserSignup($app)['address'];
        $address = $user->getAddress();
        $reg_group = $this->unfreezeGroupSignup($app);
        $students = $this->unfreezeStudentSignup($app)->getStudents();
        $group_detail = $this->unfreezeGroupDetailSignup($app);
        $payment = $this->unfreezePaymentSignup($app);

        //PER HEAD!!!
        $discount = 0;
        $deposit = $payment->fullpay ? 380 : 190;
        $total = ($deposit - $discount) * count($students);

        if (true === $request->isMethod('POST')) {
            if ( false === $this->processRegistration($app, $user, $students, $group, $reg_group)) {
                $message = 'Registration has been failed! Please try again or contact us.';
                $app['session']->getFlashBag()->add('danger', $message);
                $redirect = $app['url_generator']->generate('signup_payment', array('mode' => $mode, 'group' => $group->getId()));
                return $app->redirect($redirect);
            }

            $cardInfo = (object) array('card_num' => $payment->card_number,
                                        'exp' => implode('-', array($payment->exp_year, sprintf("%02d", $payment->exp_month))),
                                        'code' => $payment->card_ccv
                                    );
            $bill_address = $payment->address;
            $transmeta = $this->processPayment($app, $cardInfo, $total, $user, $students, $bill_address);
            if(false === $transmeta['approved']) {
                $message = 'Payment process has been failed! Please try gain or contact us.';
                $app['session']->getFlashBag()->add('danger', $message);
                $redirect = $app['url_generator']->generate('signup_payment', array('mode' => $mode, 'group' => $group->getId()));
                return $app->redirect($redirect);
            }
                //finished
                $this->savePayment($transmeta);
                $app['sessions']->clear();
                $redirect = $app['url_generator']->generate('homepage');
                return $app->redirect($redirect);
        }

        $data = array(
                    'mode' => $mode,
                    'group' => $group,
                    'user' => $user,
                    'address' => $address,
                    'group' => $group,
                    'students' => $students,
                    'group_detail' => $group_detail,
                    'payment' => $payment,
                    'total' => $total,
                );
        return $app['twig']->render('form.signup.payment.confirm.html.twig', $data);
    }


    protected function processRegistration(Application $app, $user, $students, $group, $reg_group)
    {
        // regiser user
        try {
            $user->setRole('ROLE_USER');
            $user_id = $app['repository.user']->save($user);
            $user->setUserId($user_id);
            $user->getAddress()->setUserId($user_id);
            $app['repository.address']->save($user->getAddress());
            $group_code = $group ? $group->getCode() : null;

            foreach ($students as $student) {
                if (null !== $group_code ) {
                    $app['db']->insert('signups', array(
                        'group_id' => $group->getId(),
                        'parent_id' => $user_id,
                        'student_name' => $student->name,
                        'student_dob' => $student->birthdate->getTimestamp(),
                        'level_id' => $student->level,
                        'note' => $student->note,
                        'deposit' => $deposit,
                        'discount' => $discount,
                        'group_code' => $group_code,
                        'created_at' => time()
                        ));
                }
                else {
                    foreach($reg_group[$student->student_id] as $group_id) {
                        $app['db']->insert('signups', array(
                            'group_id' => $group_id,
                            'parent_id' => $user_id,
                            'student_name' => $student->name,
                            'student_dob' => $student->birthdate->getTimestamp(),
                            'level_id' => $student->level,
                            'note' => $student->note,
                            'deposit' => $deposit,
                            'discount' => $discount,
                            'group_code' => $group_code,
                            'created_at' => time()
                        ));
                    }

                }

            }
            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    protected function processPayment(Application $app, $cardInfo, $amount, $user, $students, $bill_address)
    {
        define("AUTHORIZENET_LOG_FILE","../authorize.net.log");

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($app['authrize.net.name']);
        $merchantAuthentication->setTransactionKey($app['authrize.net.key']);
        $refId = 'ref' . time();

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cardInfo->card_num);
        $creditCard->setExpirationDate($cardInfo->exp);
        $creditCard->setCardCode($cardInfo->code);
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);

        // Order info
        // $order = new AnetAPI\OrderType();
        // $order->setInvoiceNumber($order->getInvoiceNumber());
        // $order->setDescription('Payment for '.implode(' ', $students));
        // Line Item Info
        // $lineitem = new AnetAPI\LineItemType();
        // $lineitem->setItemId("Shirts");
        // $lineitem->setName("item1");
        // $lineitem->setDescription("golf shirt");
        // $lineitem->setQuantity("1");
        // $lineitem->setUnitPrice(20.95);
        // $lineitem->setTaxable("Y");
        // Tax info
        // $tax =  new AnetAPI\ExtendedAmountType();
        // $tax->setName("level 2 tax name");
        // $tax->setAmount(4.50);
        // $tax->setDescription("level 2 tax");
        // Customer info
        $customer = new AnetAPI\CustomerDataType();
        $customer->setId($user->getUserId());
        $customer->setEmail($user->getEmail());
        // PO Number
        // $ponumber = "15";
        //Ship To Info
        // $shipto = new AnetAPI\NameAndAddressType();
        // $shipto->setFirstName("Bayles");
        // $shipto->setLastName("China");
        // $shipto->setCompany("Thyme for Tea");
        // $shipto->setAddress("12 Main Street");
        // $shipto->setCity("Pecan Springs");
        // $shipto->setState("TX");
        // $shipto->setZip("44628");
        // $shipto->setCountry("USA");
        // Bill To
        $billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName($user->getFirstName());
        $billto->setLastName($user->getLastName());
        $billto->setCompany("");
        $billto->setAddress($bill_address->getStreet());
        $billto->setCity($bill_address->getCity());
        $billto->setState($bill_address->getState());
        $billto->setZip($bill_address->getZip());
        $billto->setCountry("USA");

        //create a transaction
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType( "authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setPayment($paymentOne);
        // $transactionRequestType->setOrder($order);
        // $transactionRequestType->addToLineItems($lineitem);
        // $transactionRequestType->setTax($tax);
        // $transactionRequestType->setPoNumber($ponumber);
        $transactionRequestType->setCustomer($customer);
        $transactionRequestType->setBillTo($billto);
        // $transactionRequestType->setShipTo($shipto);
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setTransactionRequest( $transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if (null != $response)
        {
            $tresponse = $response->getTransactionResponse();
            if (($tresponse != null)  && ($tresponse->getResponseCode()=="1"))
            {
                // return array(
                // "AUTH_CODE" => $tresponse->getAuthCode(),
                // "TRANS_ID" => $tresponse->getTransId()
                // );
                return array('approved' => true, 'data' => $tresponse);
            }
            else
            {
                return array('approved' => false, 'data' => $tresponse);
            }
        }
        else
        {
          return array('approved' => false, 'data' => null);
        }
    }


    public function savePayment($transmeta)
    {
        dump($transmeta); exit();
    }
    public function save_to_db($app, $user, $group, $students, $group_detail, $payment, $customer)
    {
        // regiser user
        $user->setRole('ROLE_USER');
        $user_id = $app['repository.user']->save($user);
        $user->getAddress()->setUserId($user_id);
        $app['repository.address']->save($user->getAddress());

        // create number of groups
        $app['db']->insert('signups', array(
            'parent_id' => $user_id,
            'name' => $student->name,
            'dob' => $student->birthdate->format('Y-m-d'),
            'level_id' => $student->level,
            'note' => $student->note,
            ));

        foreach ($students as $student) {
            foreach($group[$student->student_id] as $group_id) {
                $app['db']->insert('placements', array(
                    'group_id' => $group_id,
                    'parent_id' => $user_id,
                    'student_name' => $student->name,
                    'student_dob' => $student->birthdate->getTimestamp(),
                    'level_id' => $student->level,
                    'note' => $student->note,
                    'deposit' => $deposit,
                    'discount' => $discount,
                    'group_code' => $group_code,
                    'created_at' => time()
                    ));
                }
        }
        // $app['session']->clear();
        //students

    }


       // Save the customer ID in your database so you can use it later

       // Later...
       // $customerId = getStripeCustomerId($user);




    protected function unfreezeUserSignup(Application $app)
    {
        // dump($app['session']->get('USER_SIGNUP', null));
        return $app['session']->get('USER_SIGNUP', null);
        // return $melted ? unserialize($melted) : null;
    }

    protected function freezeUserSignup(Application $app, $signup_data)
    {
        $app['session']->set('USER_SIGNUP', $signup_data);
    }

    protected function unfreezeStudentSignup(Application $app)
    {
        return $app['session']->get('STUDENT_SIGNUP', null);
    }

    protected function freezeStudentSignup(Application $app, $signup_data)
    {
        $app['session']->set('STUDENT_SIGNUP', $signup_data);
    }

    protected function unfreezeGroupSignup(Application $app)
    {
        return $app['session']->get('GROUP_SIGNUP', null);
    }

    protected function freezeGroupSignup(Application $app, $signup_data)
    {
        $app['session']->set('GROUP_SIGNUP', $signup_data);
    }

    protected function unfreezeGroupDetailSignup(Application $app)
    {
        return $app['session']->get('GROUPDETAIL_SIGNUP', null);
    }

    protected function freezeGroupDetailSignup(Application $app, $signup_data)
    {
        $app['session']->set('GROUPDETAIL_SIGNUP', $signup_data);
    }

    protected function unfreezePaymentSignup(Application $app)
    {
        return $app['session']->get('PAYMENT_SIGNUP', null);
    }

    protected function freezePaymentSignup(Application $app, $signup_data)
    {
        $app['session']->set('PAYMENT_SIGNUP', $signup_data);
    }



    public function hostSignupAction(Request $request, Application $app)
    {
        // get step parameter
        $step = $request->attributes->get('step');

        // if ($step == 1) {
        //     //init session vars
        //     $app['session']->set('HOST_STEP1', NULL);
        //     $app['session']->set('HOST_STEP2', NULL);
        //     $app['session']->set('HOST_STEP3', NULL);
        //     $app['session']->set('HOST_STEP4', NULL);
        //     $app['session']->set('HOST_STEP5', NULL);

        // }

        $entity = new HostRegistration();
        $type = new HostRegistrationType();
        switch($step) {
            case 1: //user info for User and Address
                if (null !== $app['session']->get('HOST_STEP1', null)) {
                    $step1 = unserialize($app['session']->get('HOST_STEP1'));
                    // dump($step1->getUser());
                    // dump($step1->getAddress());
                    $entity->setUser($step1->getUser());
                    $entity->setAddress($step1->getAddress());
                }
                $type->setStep(1);
                $template = 'form.host.html.twig';
                // $redirect = $app['url_generator']->generate('signup_host', array('step' => 2));
                // return $app->redirect($redirect);
                break;
            case 2: // (lesson) Session info for Session??
                if (null !== $app['session']->get('HOST_STEP2')) {
                    $step2 = unserialize($app['session']->get('HOST_STEP2'));
                    $students = $step2->getStudents();
                    // dump($students->getValues());
                    $entity=$step2;
                }
              $type->setStep(2);
              $template = 'form.host.step2.html.twig';

              // $redirect = $app['url_generator']->generate('signup_host', array('step' => 3));
              // return $app->redirect($redirect);
              break;
            case 3:
                if (null !== $app['session']->get('HOST_STEP2')) {
                    $step2 = unserialize($app['session']->get('HOST_STEP2'));
                    $students = $step2->getStudents();
                    // dump($students->getValues());
                }
                if (null !== $app['session']->get('HOST_STEP3')) {
                    $step3 = unserialize($app['session']->get('HOST_STEP3'));
                    $pref = $step3->getPreferences()->getValues();
                    // dump($pref);
                    $entity = $step3;

                }

                $formOptions['students'] = $students;
                $formOptions['dates'] = array('10/4', '10/5', '10/6');
                $formOptions['times'] = array('Morning (8-10am)',
                                            'Mid-Morning (10-12am)',
                                            'Afternoon (12-2pm)',
                                            'Mid-Afternoon (2-4pm)',
                                            'Late-Afternoon (4-6pm)',
                                             );
                // dump($formOptions);
                $type->setFormOptions($formOptions);
                $type->setStep(3);

                foreach ($students as $student) {
                    for($i = 0; $i <= 2; $i++) {
                        $pref =  new StudentPreference();
                        $pref->name = $student->name;
                        $entity->addPreference($pref);
                    }
                }
                $template = 'form.host.html.twig';
                // $redirect = $app['url_generator']->generate('signup_guest', array('step' => 4));
                // return $app->redirect($redirect);
                break;
            case 4: // (lesson) Session info for Session??
                if (null !== $app['session']->get('HOST_STEP4')) {
                    $step4 = unserialize($app['session']->get('HOST_STEP4'));

                    $entity=$step4;
                }
                $type->setStep(4);
                $template = 'form.host.html.twig';

                // $redirect = $app['url_generator']->generate('signup_guest', array('step' => 3));
                // return $app->redirect($redirect);
                break;
            case 5: // (lesson) Session info for Session??
                if (null !== $app['session']->get('HOST_STEP5')) {
                    $step5 = unserialize($app['session']->get('HOST_STEP5'));

                    $entity=$step5;
                }
                $type->setStep(5);
                $template = 'form.host.html.twig';

                // $redirect = $app['url_generator']->generate('signup_guest', array('step' => 3));
                // return $app->redirect($redirect);
                break;
            case 6: // (lesson) Session info for Session??

               $template = 'thankyou.html.twig';
               return $app['twig']->render($template);
               break;
            default:
                break;
        }

        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === TRUE) {
            $form->handleRequest($request);
            // dump($form);
            $registration = $form->getData();
            // dump($registration->getUser());exit();
            // if ($form->isValid() === TRUE) { //do vaildatation later
                // $registration = $form->getData();
                // dump($registration);

            // }

            switch($step) {
                case 1: //user info for User and Address
                    $app['session']->set('HOST_STEP1', serialize($registration));
                    // $r = unserialize($app['session']->get('HOST_STEP1'));
                    // dump($r);
                    // $template = 'form.host.step2.html.twig';
                    $redirect = $app['url_generator']->generate('signup_host', array('step' => 2));
                    return $app->redirect($redirect);
                    break;
                case 2: // (lesson) Session info for Session??
                    $app['session']->set('HOST_STEP2', serialize($registration));
                    // $r = unserialize($app['session']->get('HOST_STEP2'));
                    // dump($r);
                    // $template = 'form.host.html.twig';

                    $redirect = $app['url_generator']->generate('signup_host', array('step' => 3));
                    return $app->redirect($redirect);
                    break;
                case 3:
                    $app['session']->set('HOST_STEP3', serialize($registration));
                    // $r = unserialize($app['session']->get('HOST_STEP3'));
                    // dump($r);
                    // $template = 'form.host.html.twig';
                    $redirect = $app['url_generator']->generate('signup_host', array('step' => 4));
                    return $app->redirect($redirect);
                    break;
                case 4:
                    $app['session']->set('HOST_STEP4', serialize($registration));
                    $r = unserialize($app['session']->get('HOST_STEP3'));
                    // dump($r);exit();
                    // $template = 'form.host.html.twig';
                    $redirect = $app['url_generator']->generate('signup_host', array('step' => 5));
                    return $app->redirect($redirect);
                    break;
                case 5:
                    $app['session']->set('HOST_STEP5', serialize($registration));
                    // $r = unserialize($app['session']->get('HOST_STEP5'));
                    // dump($r);
                    // $template = 'form.host.html.twig';
                    $redirect = $app['url_generator']->generate('signup_host', array('step' => 6));
                    return $app->redirect($redirect);
                    break;
                default:
                    break;
            }
        }


        $data = array(
            'form' => $form->createView(),
        );
        // dump($form);

        return $app['twig']->render($template, $data);
    }

    public function _guestSignupAction(Request $request, Application $app)
    {
        // get step parameter
        $step = $request->attributes->get('step');

        // if ($step == 1) {
        //     //init session vars
        //     $app['session']->set('GUEST_STEP1', NULL);
        //     $app['session']->set('GUEST_STEP2', NULL);
        //     $app['session']->set('GUEST_STEP3', NULL);

        // }

        $entity = new GuestRegistration();
        $type = new GuestRegistrationType();

        switch($step) {
            case 1: //user info for User and Address
                if (null !== $app['session']->get('GUEST_STEP1', null)) {
                    $step1 = unserialize($app['session']->get('GUEST_STEP1'));
                    // dump($step1->getUser());
                    // dump($step1->getAddress());
                    // $entity->setUser($step1->getUser());
                    // $entity->setAddress($step1->getAddress());
                    $entity = $step1;
                }
                $type->setStep(1);
                $template = 'form.host.html.twig';
                // $redirect = $app['url_generator']->generate('signup_host', array('step' => 2));
                // return $app->redirect($redirect);
                break;
            case 2: // (lesson) Session info for Session??
                if (null !== $app['session']->get('GUEST_STEP2')) {
                    $step2 = unserialize($app['session']->get('GUEST_STEP2'));
                    $students = $step2->getStudents();
                    $entity->setUser($step2->getUser());
                    $entity->setAddress($step2->getAddress());
                }
              $type->setStep(2);
              $template = 'form.host.html.twig';

              // $redirect = $app['url_generator']->generate('signup_host', array('step' => 3));
              // return $app->redirect($redirect);
              break;
            case 3: // (lesson) Session info for Session??
                if (null !== $app['session']->get('GUEST_STEP3')) {
                    $step3 = unserialize($app['session']->get('GUEST_STEP3'));
                    $students = $step3->getStudents();
                    // dump($students->getValues());
                    $entity=$step3;
                }
              $type->setStep(3);
              $template = 'form.host.step2.html.twig';

              // $redirect = $app['url_generator']->generate('signup_host', array('step' => 3));
              // return $app->redirect($redirect);
              break;
            case 4:
                if (null !== $app['session']->get('GUEST_STEP3')) {
                    $step3 = unserialize($app['session']->get('GUEST_STEP3'));
                    $students = $step3->getStudents();
                    dump($students->getValues());
                    $entity = $step3;

                }
                if (null !== $app['session']->get('GUEST_STEP4')) {
                    $step4 = unserialize($app['session']->get('GUEST_STEP4'));
                    $pref = $step4->getPreferences()->getValues();
                    // dump($pref);
                }

                $formOptions['students'] = $students;
                $formOptions['dates'] = array('10/4', '10/5', '10/6');
                $formOptions['times'] = array('Morning (8-10am)',
                                            'Mid-Morning (10-12am)',
                                            'Afternoon (12-2pm)',
                                            'Mid-Afternoon (2-4pm)',
                                            'Late-Afternoon (4-6pm)',
                                             );
                // dump($formOptions);
                $type->setFormOptions($formOptions);
                $type->setStep(4);

                foreach ($students as $student) {
                    for($i = 0; $i <= 2; $i++) {
                            $pref =  new StudentPreference();
                            $pref->name = $student->name;
                            $entity->addPreference($pref);
                        }
                }
                dump($entity);
                $template = 'form.host.html.twig';
                // $redirect = $app['url_generator']->generate('signup_host', array('step' => 4));
                // return $app->redirect($redirect);
                break;
            case 5: // (lesson) Session info for Session??
                if (null !== $app['session']->get('GUEST_STEP4')) {
                    $step4 = unserialize($app['session']->get('GUEST_STEP4'));

                    $entity=$step4;
                }
                $type->setStep(4);
                $template = 'form.host.html.twig';

                // $redirect = $app['url_generator']->generate('signup_host', array('step' => 3));
                // return $app->redirect($redirect);
                break;
            case 6: // (lesson) Session info for Session??
                if (null !== $app['session']->get('GUEST_STEP5')) {
                    $step5 = unserialize($app['session']->get('GUEST_STEP5'));

                    $entity=$step5;
                }
                $type->setStep(5);
                $template = 'form.host.html.twig';

                // $redirect = $app['url_generator']->generate('signup_host', array('step' => 3));
                // return $app->redirect($redirect);
                break;
            case 7: // (lesson) Session info for Session??

               $template = 'thankyou.html.twig';
               return $app['twig']->render($template);
               break;
            default:
                break;
        }

        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === TRUE) {
            $form->handleRequest($request);
            // dump($form);
            $registration = $form->getData();
            // dump($registration->getUser());exit();
            // if ($form->isValid() === TRUE) { //do vaildatation later
                // $registration = $form->getData();
                // dump($registration);

            // }

            switch($step) {
                case 1: //group code
                    $app['session']->set('GUEST_STEP1', serialize($registration));
                    // $r = unserialize($app['session']->get('GUEST_STEP1'));
                    // dump($r);
                    // $template = 'form.host.step2.html.twig';
                    $redirect = $app['url_generator']->generate('signup_guest', array('step' => 2));
                    return $app->redirect($redirect);
                    break;
                case 2: // (lesson) Session info for Session??
                    $app['session']->set('GUEST_STEP2', serialize($registration));
                    // $r = unserialize($app['session']->get('GUEST_STEP2'));
                    // dump($r);
                    // $template = 'form.host.html.twig';

                    $redirect = $app['url_generator']->generate('signup_guest', array('step' => 3));
                    return $app->redirect($redirect);
                    break;
                case 3:
                    $app['session']->set('GUEST_STEP3', serialize($registration));
                    // $r = unserialize($app['session']->get('GUEST_STEP3'));
                    // dump($r);
                    // $template = 'form.host.html.twig';
                    $redirect = $app['url_generator']->generate('signup_guest', array('step' => 4));
                    return $app->redirect($redirect);
                    break;
                case 4:
                    $app['session']->set('GUEST_STEP4', serialize($registration));
                    $r = unserialize($app['session']->get('GUEST_STEP3'));
                    // dump($r);exit();
                    // $template = 'form.host.html.twig';
                    $redirect = $app['url_generator']->generate('signup_guest', array('step' => 5));
                    return $app->redirect($redirect);
                    break;
                case 5:
                    $app['session']->set('GUEST_STEP5', serialize($registration));
                    // $r = unserialize($app['session']->get('GUEST_STEP5'));
                    // dump($r);
                    // $template = 'form.host.html.twig';
                    $redirect = $app['url_generator']->generate('signup_guest', array('step' => 6));
                    return $app->redirect($redirect);
                    break;
                default:
                    break;
            }
        }


        $data = array(
            'form' => $form->createView(),
        );
        // dump($form);

        return $app['twig']->render($template, $data);
    }















    public function guestRegAction(Request $request, Application $app)
    {
        // $app['session']->set('signup.guest.data', null);
        // $app['session']->set('signup.guest.formtype', null);exit();
        $guest_data = unserialize($app['session']->get('signup.guest.data', null));
        dump($guest_data);
        $types = array( 'Swim\Form\Type\GroupCodeType',
                        'Swim\Form\Type\UserSignupType',
                        'Swim\Form\Type\StudentSignupType',
                        'Swim\Form\Type\GroupSignupType');

        $datas = array();
        $prev_types = $app['session']->get('signup.guest.formtype', null);
        if(null !== $prev_types) { //all done
            $types = unserialize($prev_types);
        }

        $typename = array_shift($types);
        if('Swim\Form\Type\StudentSignupType' == $typename){
            $new_signup = new StudentCollection();
            $new_signup->addStudent(new Student());
            $formData = $new_signup;
        }
        elseif ('Swim\Form\Type\GroupSignupType') {

            $type = new GroupSignupType($app, $student_collection->getStudents());
        }

        $type = new $typename($app);
        $form = $app['form.factory']->create($type, $formData);
        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                array_push($datas, $form->getData());
                // dump($formData);
                $app['session']->set('signup.guest.data', serialize($datas));
                $app['session']->set('signup.guest.formtype', serialize($types));
                $redirect = $app['url_generator']->generate('signup_guest_reg');
                return $app->redirect($redirect);
            }
        }

        $data = array('form' => $form->createView(), 'signup_for' => 'guest');
        return $app['twig']->render('form.html.twig', $data);

    }


    public function guestUserSignupAction(Request $request, Application $app)
    {
        $group = $request->attributes->get('group');
        $type = new UserSignupType();
        $form = $app['form.factory']->create($type, $this->unfreezeUserSignup($app));

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form_data = $form->getData();
                $user = $form_data['user'];
                $this->freezeUserSignup($app, $form_data);
                if (false !== $app['repository.user']->findByUsername($user->getEmail())){
                    $message = 'The email address has already been registered!';
                    $app['session']->getFlashBag()->add('danger', $message);
                    $redirect = $app['url_generator']->generate('signup_guest_user', array('group' => $group->getId()));
                    return $app->redirect($redirect);
                }
                    $this->freezeUserSignup($app, $form_data);
                    $redirect = $app['url_generator']->generate('signup_guest_student', array('group' => $group->getId()));
                    return $app->redirect($redirect);

            }
        }

        $data = array(  'form' => $form->createView(),
                        'group' => $group,
                        'signup_for' => 'guest',
                    );
        return $app['twig']->render('form.signup.user.html.twig', $data);
    }

    public function guestStudentSignupAction(Request $request, Application $app)
    {
        $group = $request->attributes->get('group');
        // dump($group);exit();
        $type = new StudentSignupType($app);
        $new_signup = new StudentCollection();
        $new_signup->addStudent(new Student());
        $entity = $new_signup;
        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $new_signup = $form->getData();
                // dump("students", $new_signup);
                //if this first submit add temp student_id for diff student next step
                $col_students = $new_signup->getStudents();
                if( null == $col_students->current()->getStudentId()) {
                    foreach ($col_students as $student) {
                        $student->setStudentId(uniqid());
                    }
                }
                $this->freezeStudentSignup($app, $new_signup);
                $redirect = $app['url_generator']->generate('signup_guest_student_pick_group', array('group' => $group->getId()));
                return $app->redirect($redirect);
            }

        }

        $data = array(  'form' => $form->createView(),
                        'group' => $group,
                        'signup_for' => 'guest',
                     );
        return $app['twig']->render('form.signup.student.html.twig', $data);

    }


    public function guestPickGroupAction(Request $request, Application $app)
    {
        $group = $request->attributes->get('group');

        // $app['session']->set('STUDENT_SIGNUP', null);exit();
        $student_collection = $this->unfreezeStudentSignup($app);
        if ( null == $student_collection ) {
            $redirect = $app['url_generator']->generate('signup_guest_student', array('group' => $group->getId()));
            return $app->redirect($redirect);
        }
        // dump($student_collection->getStudents());

        $prev_pick = $this->unfreezeGroupSignup($app);
        // dump($prev_pick);
        $entity = $prev_pick ? $prev_pick : null;
        $type = new GroupSignupType($app, $student_collection->getStudents());
        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $group_pick = $form->getData();
                // dump($group_pick);
                $this->freezeGroupSignup($app, $group_pick);
                    $redirect = $app['url_generator']->generate('signup_guest_payment', array('group' => $group->getId()));
                    return $app->redirect($redirect);
            }

        }

        $data = array('form' => $form->createView(),
                        'signup_for' => 'guest',
                        'group' => $group
                    );
        return $app['twig']->render('form.signup.group.html.twig', $data);
    }

    public function guestPaymentAction(Request $request, Application $app)
    {
        $group = $request->attributes->get('group');
        $group_pick = $this->unfreezeGroupSignup($app);
        if ( null == $group_pick ) {
            $redirect = $app['url_generator']->generate('signup_student_pick_group', array('group' => $group->getId()));
            return $app->redirect($redirect);
        }

        $prev_payment = $this->unfreezePaymentSignup($app);
        $entity = $prev_payment ? $prev_payment : null;
        $type = new PaymentType($app);
        $form = $app['form.factory']->create($type, $entity);

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
                // dump($form);
            if ($form->isValid()) {
                $new_payment = $form->getData();
                // dump($new_payment);
                $this->freezePaymentSignup($app, $new_payment);
                    $redirect = $app['url_generator']->generate('signup_guest_payment_confirm', array('group' => $group->getId()));
                    return $app->redirect($redirect);
            }

        }

        $deposit = 190;
        $data = array(
            'form' => $form->createView(),
            'signup_for' => 'guest',
            'group' => $group,
            'quantity' => $this->unfreezeStudentSignup($app)->getStudents()->count(),
            'deposit' => $deposit,
            );
        return $app['twig']->render('form.signup.payment.html.twig', $data);
    }

    public function guestConfirmAction(Request $request, Application $app)
    {

        $data = array();
        return $app['twig']->render('thankyou.html.twig', $data);
    }

}
