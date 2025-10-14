<?php


return [
    'columns' => [
        'text' => [
            'more_list_items' => ':count mais',
        ],
    ],

    'fields' => [
        'bulk_select_page' => [
            'label' => 'Selecionar/desmarcar todos os itens para ações em massa.',
        ],

        'bulk_select_record' => [
            'label' => 'Selecionar/desmarcar item :key para ações em massa.',
        ],

        'search' => [
            'label' => 'Pesquisar',
            'placeholder' => 'Pesquisar',
            'indicator' => 'Pesquisar',
        ],
    ],

    'actions' => [
        'disable_reordering' => [
            'label' => 'Concluir reordenação de registros',
        ],

        'enable_reordering' => [
            'label' => 'Reordenar registros',
        ],

        'filter' => [
            'label' => 'Filtrar',
        ],

        'group' => [
            'label' => 'Agrupar',
        ],

        'open_bulk_actions' => [
            'label' => 'Ações em massa',
        ],

        'toggle_columns' => [
            'label' => 'Alternar colunas',
        ],
    ],

    'empty' => [
        'heading' => 'Nenhum registro encontrado',
        'description' => 'Crie um :model para começar.',
    ],

    'filters' => [
        'actions' => [
            'remove' => [
                'label' => 'Remover filtro',
            ],

            'remove_all' => [
                'label' => 'Remover todos os filtros',
                'tooltip' => 'Remover todos os filtros',
            ],

            'reset' => [
                'label' => 'Redefinir',
            ],
        ],

        'heading' => 'Filtros',

        'indicator' => 'Filtros ativos',

        'multi_select' => [
            'placeholder' => 'Todos',
        ],

        'select' => [
            'placeholder' => 'Todos',
        ],

        'trashed' => [
            'label' => 'Registros excluídos',

            'only_trashed' => 'Apenas registros excluídos',

            'with_trashed' => 'Com registros excluídos',

            'without_trashed' => 'Sem registros excluídos',
        ],
    ],

    'grouping' => [
        'fields' => [
            'group' => [
                'label' => 'Agrupar por',
                'placeholder' => 'Agrupar por',
            ],

            'direction' => [
                'label' => 'Direção do agrupamento',

                'options' => [
                    'asc' => 'Crescente',
                    'desc' => 'Decrescente',
                ],
            ],
        ],
    ],

    'reorder_indicator' => 'Arraste e solte os registros em ordem.',

    'selection_indicator' => [
        'selected_count' => '1 registro selecionado|:count registros selecionados',

        'actions' => [
            'select_all' => [
                'label' => 'Selecionar todos :count',
            ],

            'deselect_all' => [
                'label' => 'Desmarcar todos',
            ],
        ],
    ],

    'sorting' => [
        'fields' => [
            'column' => [
                'label' => 'Ordenar por',
            ],

            'direction' => [
                'label' => 'Direção da ordenação',

                'options' => [
                    'asc' => 'Crescente',
                    'desc' => 'Decrescente',
                ],
            ],
        ],
    ],

    'table' => [
        'column_manager' => [
            'heading' => 'Gerenciar colunas',

            'actions' => [
                'reset' => [
                    'label' => 'Redefinir', // 👈 ESTA É A TRADUÇÃO QUE ESTAVA FALTANDO
                ],
            ],
        ],
    ],
];
