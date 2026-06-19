<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Eventos Model
 *
 * @property \App\Model\Table\ApoiosTable&\Cake\ORM\Association\HasMany $Apoios
 * @property \App\Model\Table\VotacoesTable&\Cake\ORM\Association\HasMany $Votacoes
 *
 * @method \App\Model\Entity\Evento newEmptyEntity()
 * @method \App\Model\Entity\Evento newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Evento> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Evento get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Evento findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Evento patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Evento> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Evento|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Evento saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Evento>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Evento>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Evento>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Evento> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Evento>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Evento>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Evento>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Evento> deleteManyOrFail(iterable $entities, array $options = [])
 */
class EventosTable extends Table
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

        $this->setTable('eventos');
        $this->setDisplayField('evento');
        $this->setPrimaryKey('id');

        $this->hasMany('Apoios', [
            'foreignKey' => 'evento_id',
        ]);
        $this->hasMany('Votacoes', [
            'foreignKey' => 'evento_id',
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
            ->integer('ordem')
            ->requirePresence('ordem', 'create')
            ->notEmptyString('ordem');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 25)
            ->requirePresence('nome', 'create')
            ->notEmptyString('nome');

        $validator
            ->scalar('data')
            ->maxLength('data', 50)
            ->requirePresence('data', 'create')
            ->notEmptyString('data');

        $validator
            ->scalar('local')
            ->maxLength('local', 25)
            ->requirePresence('local', 'create')
            ->notEmptyString('local');

        return $validator;
    }
}
