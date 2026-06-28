<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Apoios Model
 *
 * @property \App\Model\Table\EventosTable&\Cake\ORM\Association\BelongsTo $Eventos
 * @property \App\Model\Table\ItemsTable&\Cake\ORM\Association\HasMany $Items
 *
 * @method \App\Model\Entity\Apoio newEmptyEntity()
 * @method \App\Model\Entity\Apoio newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Apoio> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Apoio get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Apoio findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Apoio patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Apoio> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Apoio|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Apoio saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Apoio>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apoio>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Apoio>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apoio> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Apoio>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apoio>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Apoio>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apoio> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ApoiosTable extends Table
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

        $this->setTable('apoios');
        $this->setDisplayField('caderno');
        $this->setPrimaryKey('id');

        $this->belongsTo('Eventos', [
            'foreignKey' => 'evento_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Items', [
            'foreignKey' => 'apoio_id',
        ]);
        $this->belongsTo('Gts', [
            'foreignKey' => 'gt_id',
            'joinType' => 'LEFT',
            'propertyName' => 'gt_entity',
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
            ->scalar('nomedoevento')
            ->maxLength('nomedoevento', 15)
            ->allowEmptyString('nomedoevento');

        $validator
            ->integer('evento_id')
            ->notEmptyString('evento_id');

        $validator
            ->scalar('caderno')
            ->requirePresence('caderno', 'create')
            ->notEmptyString('caderno');

        $validator
            ->integer('numero_texto')
            ->requirePresence('numero_texto', 'create')
            ->notEmptyString('numero_texto');

        $validator
            ->scalar('tema')
            ->requirePresence('tema', 'create')
            ->notEmptyString('tema');

        $validator
            ->scalar('gt')
            ->maxLength('gt', 50)
            ->allowEmptyString('gt');

        $validator
            ->integer('gt_id')
            ->allowEmptyString('gt_id');

        $validator
            ->scalar('titulo')
            ->maxLength('titulo', 256)
            ->requirePresence('titulo', 'create')
            ->notEmptyString('titulo');

        $validator
            ->scalar('autor')
            ->requirePresence('autor', 'create')
            ->notEmptyString('autor');

        $validator
            ->scalar('texto')
            ->maxLength('texto', 16777215)
            ->requirePresence('texto', 'create')
            ->notEmptyString('texto');

        return $validator;
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
        $rules->add($rules->existsIn(['evento_id'], 'Eventos'), ['errorField' => 'evento_id']);

        return $rules;
    }
}
