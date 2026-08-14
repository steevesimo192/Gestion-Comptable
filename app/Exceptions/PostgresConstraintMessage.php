<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class PostgresConstraintMessage extends Exception
{
    //
    /**
     * Essaie de transformer une QueryException en reponse JSON propre.
     * Retourne null si l'exception ne correspond a aucun cas connu
     * (dans ce cas, laisse Laravel gerer l'exception normalement).
     */
    public static function toResponse(QueryException $e): ?JsonResponse
    {
        $message = $e->getMessage();

        // Trigger PostgreSQL : ecriture/ligne comptabilisee immuable
        if (str_contains($message, 'est immuable') || str_contains($message, 'comptabilisee')) {
            return self::error(
                "Cette ecriture est comptabilisee et ne peut plus etre modifiee ou supprimee. ".
                "Utilisez une extourne (ecriture_extournee_id) pour la corriger.",
                409
            );
        }

        // Trigger PostgreSQL : journal d'audit immuable
        if (str_contains($message, 'journaux_audit') && str_contains($message, 'immuable')) {
            return self::error(
                "Le journal d'audit ne peut jamais etre modifie ni supprime.",
                409
            );
        }

        // Contrainte CHECK : debit/credit exclusifs
        if (str_contains($message, 'debit_credit_exclusif')) {
            return self::error(
                "Une ligne d'ecriture ne peut pas avoir a la fois un debit et un credit.",
                422
            );
        }

        // Contrainte CHECK : ecriture equilibree si postee
        if (str_contains($message, 'equilibree') || str_contains($message, 'ecritures_equilibrees_si_postees')) {
            return self::error(
                "Le total debit doit etre egal au total credit pour valider ou comptabiliser cette ecriture.",
                422
            );
        }

        // Contrainte CHECK : dates incoherentes
        if (str_contains($message, 'dates_coherentes')) {
            return self::error(
                "La date de fin ne peut pas etre anterieure a la date de debut.",
                422
            );
        }

        // Contrainte CHECK : montants/taux negatifs
        if (str_contains($message, 'positif') || str_contains($message, 'positifs')) {
            return self::error(
                "Ce montant ou ce taux doit etre positif.",
                422
            );
        }

        // Violation de cle unique PostgreSQL (code SQLSTATE 23505),
        // au cas ou une contrainte unique n'aurait pas ete interceptee
        // en amont par le Form Request (ex: appel direct au modele).
        if (str_contains($message, 'duplicate key value violates unique constraint')) {
            return self::error(
                "Cette valeur existe deja et doit rester unique.",
                409
            );
        }

        // Violation de cle etrangere (code SQLSTATE 23503)
        if (str_contains($message, 'violates foreign key constraint')) {
            return self::error(
                "Impossible d'effectuer cette action : un element lie n'existe pas ou est encore utilise ailleurs.",
                409
            );
        }

        return null;
    }

    private static function error(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
