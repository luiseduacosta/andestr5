<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Gts Model
 *
 * @method \App\Model\Entity\Gt newEmptyEntity()
 * @method \App\Model\Entity\Gt newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Gt> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Gt get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Gt findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Gt patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Gt> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Gt|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Gt saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Gt delete(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Gt saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Gt deleteMany(iterable $entities, array $options = [])
 */
class GtsTable extends Table
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

        $this->setTable('gts');
        $this->setDisplayField('sigla');
        $this->setPrimaryKey('id');

        $this->hasMany('Apoios', [
            'foreignKey' => 'gt_id',
            'propertyName' => 'apoios_list',
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
            ->scalar('sigla')
            ->maxLength('sigla', 20)
            ->requirePresence('sigla', 'create')
            ->notEmptyString('sigla');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 100)
            ->allowEmptyString('nome');

        $validator
            ->scalar('outras')
            ->maxLength('outras', 50)
            ->allowEmptyString('outras');

        return $validator;
    }
}
