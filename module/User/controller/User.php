<?php
class User_User_Controller extends MiniMVC_Controller
{
    public function indexAction($params)
    {
        $showPerPage = 20;
        $currentPage = !empty($_GET['p']) ? $_GET['p'] : 1;
        $query = UserTable::getInstance()->load(null, null, 'name ASC', $showPerPage, ($currentPage - 1) * $showPerPage, 'query');

        $this->view->entries = $query->build();

        $this->view->pager = $this->registry->helper->pager->get(
                $query->count(),
                $showPerPage,
                $this->registry->helper->url->get('user.userIndex') . '(?p={page})',
                $currentPage,
                7,
                false
        );

        $this->registry->helper->meta->setTitle($this->view->t->userIndexTitle);
        $this->registry->helper->meta->setDescription($this->view->t->userIndexMetaDescription);
    }

    public function showAction($params)
    {
        if (!$params['model']) {
            return $this->delegate404();
        } else {
            $user = $params['model'];
        }

        if ($this->registry->rights->roleHasRight($user->role, 'guest')) {
            return $this->delegate404();
        }

        $this->view->model = $user;

        $this->registry->helper->meta->setTitle($this->view->t->userShowTitle(array('name' => htmlspecialchars($this->view->model->name))));
        $this->registry->helper->meta->setDescription($this->view->t->userShowMetaDescription(array('name' => htmlspecialchars($this->view->model->name))));
    }

    public function loginAction($params)
    {
        $form = UserTable::getInstance()->getLoginForm(null, array(
            'route' => 'user.userProcessLogin'
        ));

        $this->view->form = $form;

        $this->registry->helper->meta->setTitle($this->view->t->userLoginTitle);
        $this->registry->helper->meta->setDescription($this->view->t->userLoginDescription);
    }

    public function processLoginAction($params)
    {
        $form = UserTable::getInstance()->getLoginForm(null, array(
            'route' => 'user.userProcessLogin'
        ));

        $model = $form->getModel();
        $success = false;
        $message = '';

        if ($form->validate())
        {
            $success = true;
            $model = $form->getModel();
            $this->registry->guard->setUser($model->id, $model->role, true);
            $this->registry->guard->setAuthToken($model->auth_token);
            $this->registry->guard->email = $model->email;
            $this->registry->guard->slug = $model->slug;
            $this->registry->guard->name = $model->name;

            $message = $this->view->t->userLoginSuccessMessage;
            if ($this->registry->layout->getFormat() === null) {
                $this->registry->helper->messages->add($message, 'success');
                $form->successRedirect('user.userShow', array('slug' => $model->slug));
            }
        }

        if ($this->registry->layout->getFormat() === null) {
            $form->errorRedirect('user.userLogin');
        }

        $this->view->form = $form;
        $this->view->model = $model;
        $this->view->success = $success;
        $this->view->message = $message;
    }

    public function newAction($params)
    {
        $form = UserTable::getInstance()->getRegisterForm(null, array(
            'route' => 'user.userCreate'
        ));

        $this->view->form = $form;

        $this->registry->helper->meta->setTitle($this->view->t->userNewTitle);
        $this->registry->helper->meta->setDescription($this->view->t->userNewMetaDescription);
    }

    public function createAction($params)
    {
        $form = UserTable::getInstance()->getRegisterForm(null, array(
            'route' => 'user.userCreate'
        ));

        $model = $form->getModel();
        $success = false;
        $message = '';

        if ($form->validate())
        {
            $form->updateModel();
            if ($model->save()) {
                $success = true;
                
                $this->registry->guard->setUser($model->id, $model->role, true);
                $this->registry->guard->setAuthToken($model->auth_token);
                $this->registry->guard->email = $model->email;
                $this->registry->guard->slug = $model->slug;
                $this->registry->guard->name = $model->name;

                $message = $this->view->t->userCreateSuccessMessage;
                if ($this->registry->layout->getFormat() === null) {
                    $this->registry->helper->messages->add($message, 'success');
                    $form->successRedirect('user.userShow', array('slug' => $model->slug));
                }
            } else {
                $form->setError($this->view->t->userCreateErrorMessage);
            }
        }

        if ($this->registry->layout->getFormat() === null) {
            $form->errorRedirect('user.userNew');
        }

        $this->view->form = $form;
        $this->view->model = $model;
        $this->view->success = $success;
        $this->view->message = $message;
    }

    public function editAction($params)
    {
        if (!$params['model']) {
            return $this->delegate404();
        }

        $model = $params['model'];

        if (!$this->registry->guard->getId()) {
            return $this->delegate401($this->view->t->userEditNotLoggedInError);
        } elseif ($this->registry->guard->getId() != $model->id) {
            return $this->delegate403($this->view->t->userEditInvalidUserError);
        }

        $form = UserTable::getInstance()->getPasswordForm($model, array(
            'route' => 'user.userUpdate',
            'parameter' => array('slug' => $model->slug)
        ));

        $this->view->form = $form;
        $this->view->model = $model;

        $this->registry->helper->meta->setTitle($this->view->t->userEditTitle);
        $this->registry->helper->meta->setDescription($this->view->t->userEditMetaDescription);
    }

    public function updateAction($params)
    {
        if (!$params['model']) {
            return $this->delegate404();
        }

        $model = $params['model'];

        if (!$this->registry->guard->getId()) {
            return $this->delegate401($this->view->t->userEditNotLoggedInError);
        } elseif ($this->registry->guard->getId() != $model->id) {
            return $this->delegate403($this->view->t->userEditInvalidUserError);
        }

        $form = UserTable::getInstance()->getPasswordForm($model, array(
            'route' => 'user.userUpdate',
            'parameter' => array('slug' => $model->slug)
        ));
        $success = false;
        $message = '';

        if ($form->validate())
        {
            $form->updateModel();
            if ($model->save()) {
                $success = true;
                $message = $this->view->t->userUpdateSuccessMessage;
                if ($this->registry->layout->getFormat() === null) {
                    $this->registry->helper->messages->add($message, 'success');
                    $form->successRedirect('user.userShow', array('slug' => $model->slug));
                }
            } else {
                $this->view->form->setError($this->view->t->userUpdateErrorMessage);
            }
        }

        if ($this->registry->layout->getFormat() === null) {
            $form->errorRedirect('user.userEdit', array('slug' => $model->slug));
        }

        $this->view->form = $form;
        $this->view->model = $model;
        $this->view->success = $success;
        $this->view->message = $message;
    }

    public function deleteAction($params)
    {
        if (!$params['model']) {
            return $this->delegate404();
        }

        $model = $params['model'];

        if (!$this->registry->guard->getId()) {
            return $this->delegate401($this->view->t->userDeleteNotLoggedInError);
        } elseif ($this->registry->guard->getId() != $model->id) {
            return $this->delegate403($this->view->t->userDeleteInvalidUserError);
        }

        $success = $model->delete();

        if ($success) {
            $this->registry->guard->setUser();
            $message = $this->view->t->userDeleteSuccessMessage(array('name' => htmlspecialchars($model->name)));
            if ($params['_format'] == 'default') {
                $this->registry->helper->messages->add($message, 'success');
                return $this->redirect('user.userIndex');
            }
        } else {
            $message = $this->view->t->userDeleteErrorMessage(array('name' => htmlspecialchars($model->name)));
            if ($params['_format'] == 'default') {
               $this->registry->helper->messages->add($message, 'error');
                return $this->redirect('user.userIndex');
            }
        }

        $this->view->model = $model;
        $this->view->success = $success;
        $this->view->message = $message;
    }

    public function widgetAction($params)
    {
    }

}
