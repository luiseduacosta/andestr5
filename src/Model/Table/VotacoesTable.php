<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Votacoes Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\EventosTable&\Cake\ORM\Association\BelongsTo $Eventos
 * @property \App\Model\Table\ItemsTable&\Cake\ORM\Association\BelongsTo $Items
 * @property \App\Model\Table\ItemsTable&\Cake\ORM\Association\BelongsTo $VotacaoItem
 *
 * @method \App\Model\Entity\Votacao newEmptyEntity()
 * @method \App\Model\Entity\Votacao newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Votacao> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Votacao get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Votacao findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Votacao patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Votacao> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Votacao|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Votacao saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Votacao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Votacao>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Votacao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Votacao> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Votacao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Votacao>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Votacao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Votacao> deleteManyOrFail(iterable $entities, array $options = [])
 */
class VotacoesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('votacoes');
        $this->setDisplayField('item');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Eventos', [
            'foreignKey' => 'evento_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id',
            'joinType' => 'INNER',
            'propertyName' => 'votacao_item',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('evento_id')
            ->notEmptyString('evento_id');

        $validator
            ->integer('grupo')
            ->requirePresence('grupo', 'create')
            ->notEmptyString('grupo');

        $validator
            ->integer('tr')
            ->requirePresence('tr', 'create')
            ->notEmptyString('tr');

        $validator
            ->integer('item_id')
            ->notEmptyString('item_id');

        $validator
            ->scalar('item')
            ->maxLength('item', 10)
            ->requirePresence('item', 'create')
            ->notEmptyString('item');

        $validator
            ->scalar('resultado')
            ->maxLength('resultado', 12)
            ->requirePresence('resultado', 'create')
            ->notEmptyString('resultado');

        $validator
            ->scalar('votacao')
            ->maxLength('votacao', 10)
            ->requirePresence('votacao', 'create')
            ->notEmptyString('votacao')
            ->add('votacao', 'formato', [
                'rule' => ['custom', '/^\d{1,2}\/\d{1,2}\/\d{1,2}$/'],
                'message' => __('O campo votação deve estar no formato XX/XX/XX (ex: 15/6/0).'),
            ]);

        $validator
            ->scalar('item_modificada')
            ->requirePresence('item_modificada', 'create')
            ->allowEmptyString('item_modificada');

        $validator
            ->dateTime('data')
            ->notEmptyDateTime('data');

        $validator
            ->scalar('observacoes')
            ->allowEmptyString('observacoes');

        return $validator;
    }

    /**
     * KISS: TR está suprimida ⇔ todos os itens rejeitados.
     */
    public function isTrSuprimida(int $tr, int $eventoId): bool
    {
        $votos = $this->find()
            ->select(['resultado'])
            ->distinct()
            ->where(['tr' => $tr, 'evento_id' => $eventoId])
            ->all();

        return !$votos->isEmpty() && $votos->every(fn($v) => $v->resultado === 'Rejeitado');
    }

    /**
     * KISS: TR aprovada sem modificações ⇔ todos aprovados E nenhum modificado.
     */
    public function isTrAprovada(int $tr, int $eventoId): bool
    {
        $votos = $this->find()
            ->select(['resultado', 'item_modificada'])
            ->where(['tr' => $tr, 'evento_id' => $eventoId])
            ->all();

        if ($votos->isEmpty()) {
            return false;
        }

        return $votos->every(fn($v) =>
            $v->resultado === 'Aprovado' && ($v->item_modificada === '' || $v->item_modificada === null)
        );
    }

    /**
     * Custom finder: itens de uma TR sem voto registrado no evento.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param array $options Espera 'grupo', 'tr', 'evento_id'
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findItensSemVoto(SelectQuery $query, array $options): SelectQuery
    {
        $grupo = (int)($options['grupo'] ?? 0);
        $tr = (int)($options['tr'] ?? 0);
        $eventoId = (int)($options['evento_id'] ?? 0);

        if ($grupo === 0 || $tr === 0 || $eventoId === 0) {
            return $query->where(['1 = 0']);
        }

        // Subquery: IDs dos itens que JÁ têm voto neste evento
        $votados = $this->find()
            ->select(['Votacoes.item_id'])
            ->distinct()
            ->where(['Votacoes.evento_id' => $eventoId]);

        return $this->Items->find()
            ->select(['Items.id', 'Items.item', 'Items.tr', 'Items.texto'])
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $eventoId,
                'Items.tr' => $tr,
                'Items.id NOT IN' => $votados,
            ])
            ->order(['Items.item' => 'ASC']);
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['evento_id'], 'Eventos'), ['errorField' => 'evento_id']);
        $rules->add($rules->existsIn(['item_id'], 'Items'), ['errorField' => 'item_id']);

        return $rules;
    }
}
