<?php

namespace Swim\Controller;

use Swim\Entity\User;
use Swim\Entity\Student;
use Swim\Entity\StudentCollection;
use Swim\Entity\Address;
use Swim\Entity\Registration;
use Swim\Entity\HostRegistration;
use Swim\Entity\GuestRegistration;
use Swim\Entity\StudentPreference;
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

use Swim\Form\Type\HostRegistrationType;
use Swim\Form\Type\GuestRegistrationType;
use Swim\Form\Type\StudentPreferenceType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class SignupController
{

    protected $isDone;

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

    public function guestSignupAction(Request $request, Application $app)
    {
        $group = $request->attributes->get('group');
        $form = $app['form.factory']->create(new GroupCodeType());

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $group = $form->getData();
                $group = $app['repository.group']->findByCode($group->getCode());
                if($group) {
                    $redirect = $app['url_generator']->generate('signup_guest_user',
                        array('group' => $group->getId()));
                    return $app->redirect($redirect);
                } else {
                    $message = 'Please try again with a valid Group Code.';
                    $app['session']->getFlashBag()->add('danger', $message);
                }

            }
        }

        $data = array(  'form' => $form->createView(),
                        'signup_for' => 'guest',
                    );
        return $app['twig']->render('form.signup.groupcode.html.twig', $data);
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
                // dump($form_data);
                // $user = $form_data['user'];
                // $address = $form_data['address'];
                // $user->setUsername($user->getEmail());
                // $user->setRole('ROLE_USER');

                // $new_user_id = $app['repository.user']->save($user);
                // if($new_user_id) {
                //     $address->user_id = $new_user_id;
                    // $app['repository.user']->insertAddress($address);
                    $this->freezeUserSignup($app, $form_data);
                    $redirect = $app['url_generator']->generate('signup_guest_student', array('group' => $group->getId()));
                    return $app->redirect($redirect);
                // }
                // else {
                //     $message = 'User signup failed.';
                //     $app['session']->getFlashBag()->add('error', $message);
                // }

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



    /**
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userSignupAction(Request $request, Application $app)
    {

        $type = new UserSignupType();
        $form = $app['form.factory']->create($type, $this->unfreezeUserSignup($app));

        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form_data = $form->getData();
                $user = $form_data['user'];
                //check the email is already been registered
                if (false !== $app['repository.user']->findByUsername($user->getEmail())){
                    $message = 'The email address has already been registered!';
                    $app['session']->getFlashBag()->add('danger', $message);
                    $redirect = $app['url_generator']->generate('signup_user');
                    return $app->redirect($redirect);
                }
                // set username with email, set ROLE
                $user->setRole('ROLE_USER');
                // try {
                //     $user_id = $app['repository.user']->save($user);
                //     $user->getAddress()->setUserId($user_id);
                //     $app['repository.address']->save($user->getAddress());
                // } catch (\Exception $e) {

                // }
                $this->freezeUserSignup($app, $form_data);
                $redirect = $app['url_generator']->generate('signup_student');
                return $app->redirect($redirect);
            }
        }

        $data = array('form' => $form->createView(), 'signup_for' => 'host');
        return $app['twig']->render('form.signup.user.html.twig', $data);
    }

    public function studentSignupAction(Request $request, Application $app)
    {
        $user = $this->unfreezeUserSignup($app);
        if ( null == $user ) {
            $redirect = $app['url_generator']->generate('signup_user');
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
                $redirect = $app['url_generator']->generate('signup_student_pick_group');
                return $app->redirect($redirect);
            }

        }

        $data = array(
            'form' => $form->createView(),
            'signup_for' => 'host',
            );
        return $app['twig']->render('form.signup.student.html.twig', $data);
    }

    public function pickGroupAction(Request $request, Application $app)
    {
        // $app['session']->set('STUDENT_SIGNUP', null);exit();
        $student_collection = $this->unfreezeStudentSignup($app);
        if ( null == $student_collection ) {
            $redirect = $app['url_generator']->generate('signup_student');
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
                    $redirect = $app['url_generator']->generate('signup_group_detail');
                    return $app->redirect($redirect);
            }

        }

        $data = array('form' => $form->createView(),
                        'signup_for' => 'host',
                        );
        return $app['twig']->render('form.signup.group.html.twig', $data);
    }

    public function groupDetailAction(Request $request, Application $app)
    {
        // $app['session']->set('STUDENT_SIGNUP', null);exit();
        $group = $this->unfreezeGroupSignup($app);
        if ( null == $group ) {
            $redirect = $app['url_generator']->generate('signup_student_pick_group');
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
                // dump($group_detail);
                $this->freezeGroupDetailSignup($app, $group_detail);
                    $redirect = $app['url_generator']->generate('signup_payment');
                    return $app->redirect($redirect);
            }
        }

        $data = array('form' => $form->createView(),
            'signup_for' => 'host');
        return $app['twig']->render('form.signup.groupdetail.html.twig', $data);
    }

    public function paymentAction(Request $request, Application $app)
    {
        $group_pick = $this->unfreezeGroupSignup($app);
        if ( null == $group_pick ) {
            $redirect = $app['url_generator']->generate('signup_student_pick_group');
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
                    $redirect = $app['url_generator']->generate('signup_payment_confirm');
                    return $app->redirect($redirect);
            }

        }

        $deposit = 190;
        $data = array(
            'form' => $form->createView(),
            'deposit' => $deposit,
            'quantity' => $this->unfreezeStudentSignup($app)->getStudents()->count(),
            'signup_for' => 'host',
            );
        return $app['twig']->render('form.signup.payment.html.twig', $data);
    }

    public function confirmAction(Request $request, Application $app)
    {

        $group = $request->attributes->get('group');
        $signup_for = $group ? 'guest' : 'host';

        $user = $this->unfreezeUserSignup($app)['user'];
        $address = $this->unfreezeUserSignup($app)['address'];
        $group = $this->unfreezeGroupSignup($app);
        $students = $this->unfreezeStudentSignup($app)->getStudents();
        $group_detail = $this->unfreezeGroupDetailSignup($app);
        $payment = $this->unfreezePaymentSignup($app);

        $total = 0;

        $data = array(
            'user' => $user,
            'address' => $address,
            'group' => $group,
            'students' => $students,
            'group_detail' => $group_detail,
            'payment' => $payment,
            'total' => $total,
            'signup_for' => $signup_for,
            );
        return $app['twig']->render('form.signup.payment.confirm.html.twig', $data);
    }



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
}
