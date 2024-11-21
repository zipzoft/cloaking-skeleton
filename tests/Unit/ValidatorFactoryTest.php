<?php

use App\ValidatorFactory;
use App\Contracts\VisitorValidatorInterface;

test('adds validator to the list', function () {
    $factory = new ValidatorFactory();
    $validator = \Mockery::mock(VisitorValidatorInterface::class);
    
    $factory->addValidator($validator);
    
    expect($factory->getValidators())->toHaveCount(1)
        ->and($factory->getValidators()[0])->toBe($validator);
});

test('adds multiple validators to the list', function () {
    $factory = new ValidatorFactory();
    $validator1 = \Mockery::mock(VisitorValidatorInterface::class);
    $validator2 = \Mockery::mock(VisitorValidatorInterface::class);
    $validator3 = \Mockery::mock(VisitorValidatorInterface::class);
    
    $factory->addValidator($validator1);
    $factory->addValidator($validator2);
    $factory->addValidator($validator3);
    
    expect($factory->getValidators())->toHaveCount(3)
        ->and($factory->getValidators()[0])->toBe($validator1)
        ->and($factory->getValidators()[1])->toBe($validator2)
        ->and($factory->getValidators()[2])->toBe($validator3);
});

test('returns empty array when no validators added', function () {
    $factory = new ValidatorFactory();
    
    expect($factory->getValidators())->toBeArray()
        ->and($factory->getValidators())->toBeEmpty();
});
