<?php

return [
    'required' => 'O campo :attribute e obrigatorio.',
    'string' => 'O campo :attribute deve ser um texto.',
    'email' => 'O campo :attribute deve ser um e-mail valido.',
    'unique' => 'Este :attribute ja esta em uso.',
    'confirmed' => 'A confirmacao de :attribute nao confere.',
    'min' => [
        'string' => 'O campo :attribute deve ter no minimo :min caracteres.',
        'numeric' => 'O campo :attribute deve ter no minimo :min.',
    ],
    'max' => [
        'string' => 'O campo :attribute deve ter no maximo :max caracteres.',
        'numeric' => 'O campo :attribute deve ter no maximo :max.',
    ],
    'digits' => 'O campo :attribute deve ter :digits digitos.',
    'integer' => 'O campo :attribute deve ser um numero inteiro.',
    'numeric' => 'O campo :attribute deve ser numerico.',
    'date' => 'O campo :attribute deve ser uma data valida.',
    'exists' => 'O valor informado para :attribute e invalido.',
    'mimes' => 'O arquivo deve ser do tipo: :values.',
    'image' => 'O arquivo deve ser uma imagem.',
    'in' => 'O campo :attribute selecionado e invalido.',

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmacao de senha',
        'cpf' => 'cpf',
        'telefone' => 'telefone',
        'pix' => 'pix',
        'endereco' => 'endereco',
        'filial_id' => 'filial',
        'data_diaria' => 'data da diaria',
        'daily_shift_id' => 'turno',
        'dias_pagos' => 'dias pagos',
    ],
];
