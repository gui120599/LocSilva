<?php

namespace App\Helper;

class FormatHelper
{
    public static function formatCpfCnpj(?string $value): string
    {
        if (empty($value)) {
            return '-';
        }

        // Remove formatação
        $cpfCnpj = preg_replace('/\D/', '', $value);

        // CPF
        if (strlen($cpfCnpj) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfCnpj);
        }

        // CNPJ
        if (strlen($cpfCnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cpfCnpj);
        }

        return $value;
    }

    public static function formatTelefone(?string $value): string
    {
        if (empty($value)) return '-';

        $telefone = preg_replace('/\D/', '', $value);

        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        }

        if (strlen($telefone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }

        if (strlen($telefone) === 9) {
            return preg_replace('/(\d{5})(\d{4})/', '$1-$2', $telefone);
        }

        if (strlen($telefone) === 8) {
            return preg_replace('/(\d{4})(\d{4})/', '$1-$2', $telefone);
        }

        return $value;
    }

    public static function formatCep(?string $value): string
    {
        if (empty($value)) return '-';

        $cep = preg_replace('/\D/', '', $value);

        if (strlen($cep) === 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
        }

        return $value;
    }
}
