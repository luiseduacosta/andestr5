<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * beforeFilter method
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        $session = $this->request->getSession();
        $identity = $this->Authentication->getIdentity();
        $eventosTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos');

        if ($identity && $identity->role === 'relator') {
            // Relator usa o evento ativo definido pelo admin/editor, fallback ao último
            $ativo = $eventosTable->find()->where(['ativo' => true])->first();
            if ($ativo) {
                $session->write('selected_evento_id', $ativo->id);
            } elseif (!$session->read('selected_evento_id')) {
                $lastEvento = $eventosTable->find()->order(['id' => 'DESC'])->first();
                if ($lastEvento) {
                    $session->write('selected_evento_id', $lastEvento->id);
                }
            }
        } else {
            // Admin/editor: mantém o evento da sessão, fallback para o último
            $selectedEventoId = $session->read('selected_evento_id');
            if (!$selectedEventoId) {
                $lastEvento = $eventosTable->find()->order(['id' => 'DESC'])->first();
                if ($lastEvento) {
                    $session->write('selected_evento_id', $lastEvento->id);
                }
            }
        }
    }

    /**
     * beforeRender method
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        parent::beforeRender($event);

        $session = $this->request->getSession();
        $selectedEventoId = $session->read('selected_evento_id');

        $selectedEvento = null;
        $allEventos = [];

        if ($selectedEventoId) {
            $eventosTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos');
            $selectedEvento = $eventosTable->find()->where(['id' => $selectedEventoId])->first();

            $identity = $this->components()->has('Authentication') ? $this->Authentication->getIdentity() : $this->request->getAttribute('identity');
            if ($identity && ($identity->role === 'admin' || $identity->role === 'editor')) {
                $allEventos = $eventosTable->find('list', keyField: 'id', valueField: 'nome')->toArray();
            }
        }

        $this->set(compact('selectedEvento', 'allEventos'));
    }
}
