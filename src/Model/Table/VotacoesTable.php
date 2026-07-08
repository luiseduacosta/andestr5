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
            ->allowEmptyString('item_modificada')
            ->add('item_modificada', 'requiredWhenModificada', [
                'rule' => function ($value, $context) {
                    $resultado = $context['data']['resultado'] ?? '';
                    if ($resultado === 'modificada') {
                        return !empty(trim((string)$value));
                    }
                    return true;
                },
                'message' => __('O campo "Item Modificada" é obrigatório quando o resultado é "Modificada".'),
            ]);

        $validator
            ->dateTime('data')
            ->notEmptyDateTime('data');

        $validator
            ->scalar('observacoes')
            ->allowEmptyString('observacoes');

        $validator
            ->boolean('destaque_minoria')
            ->allowEmptyString('destaque_minoria');

        return $validator;
    }

    /**
     * TR está suprimida ⇔ todos os itens da TR foram votados como 'suprimida'.
     * Verifica que a quantidade de votos 'suprimida' é igual ao total de itens da TR.
     */
    public function isTrSuprimida(int $tr, int $eventoId): bool
    {
        // Total de itens na TR (excluindo .99)
        $totalItens = $this->Items->find()
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $eventoId,
                'Items.tr' => $tr,
                'Items.item NOT LIKE' => '%.99',
            ])
            ->count();

        if ($totalItens === 0) {
            return false;
        }

        // Contar votos 'suprimida' na TR
        $votosSuprimida = $this->find()
            ->where([
                'tr' => $tr,
                'evento_id' => $eventoId,
                'resultado' => 'suprimida',
            ])
            ->count();

        return $votosSuprimida === $totalItens;
    }

    /**
     * TR aprovada sem modificações ⇔ todos os itens foram votados como 'aprovada' sem modificação.
     * Verifica que a quantidade de votos é igual ao total de itens da TR.
     */
    public function isTrAprovada(int $tr, int $eventoId): bool
    {
        // Total de itens na TR (excluindo .99)
        $totalItens = $this->Items->find()
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $eventoId,
                'Items.tr' => $tr,
                'Items.item NOT LIKE' => '%.99',
            ])
            ->count();

        if ($totalItens === 0) {
            return false;
        }

        // Contar votos 'aprovada' sem modificação na TR
        $votosAprovados = $this->find()
            ->where([
                'tr' => $tr,
                'evento_id' => $eventoId,
                'resultado' => 'aprovada',
            ])
            ->andWhere(function ($exp) {
                return $exp->or([
                    ['item_modificada' => ''],
                    ['item_modificada IS' => null],
                ]);
            })
            ->count();

        return $votosAprovados === $totalItens;
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
        $userId = (int)($options['user_id'] ?? 0);

        if ($grupo === 0 || $tr === 0 || $eventoId === 0 || $userId === 0) {
            return $query->where(['1 = 0']);
        }

        // Subquery: IDs dos itens que JÁ têm voto deste usuário neste evento
        $votados = $this->find()
            ->select(['Votacoes.item_id'])
            ->distinct()
            ->where([
                'Votacoes.evento_id' => $eventoId,
                'Votacoes.user_id' => $userId,
            ]);

        return $this->Items->find()
            ->select(['Items.id', 'Items.item', 'Items.tr', 'Items.texto'])
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $eventoId,
                'Items.tr' => $tr,
                'Items.id NOT IN' => $votados,
                'Items.item NOT LIKE' => '%.99',
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
