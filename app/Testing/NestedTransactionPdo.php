<?php

declare(strict_types=1);

namespace App\Testing;

use PDO;
use PDOException;

final class NestedTransactionPdo extends PDO
{
    private int $transactionDepth = 0;

    public function beginTransaction(): bool
    {
        if ($this->transactionDepth === 0) {
            $started = parent::beginTransaction();
            if ($started) {
                $this->transactionDepth = 1;
            }

            return $started;
        }

        $savepoint = $this->savepointName($this->transactionDepth + 1);
        $created = $this->exec('SAVEPOINT ' . $savepoint) !== false;

        if ($created) {
            $this->transactionDepth++;
        }

        return $created;
    }

    public function commit(): bool
    {
        if ($this->transactionDepth === 0) {
            throw new PDOException('No active transaction to commit.');
        }

        if ($this->transactionDepth === 1) {
            $committed = parent::commit();
            if ($committed) {
                $this->transactionDepth = 0;
            }

            return $committed;
        }

        $savepoint = $this->savepointName($this->transactionDepth);
        $released = $this->exec('RELEASE SAVEPOINT ' . $savepoint) !== false;

        if ($released) {
            $this->transactionDepth--;
        }

        return $released;
    }

    public function rollBack(): bool
    {
        if ($this->transactionDepth === 0) {
            throw new PDOException('No active transaction to roll back.');
        }

        if ($this->transactionDepth === 1) {
            $rolledBack = parent::rollBack();
            if ($rolledBack) {
                $this->transactionDepth = 0;
            }

            return $rolledBack;
        }

        $savepoint = $this->savepointName($this->transactionDepth);
        $rolledBack = $this->exec('ROLLBACK TO SAVEPOINT ' . $savepoint) !== false;

        if ($rolledBack) {
            $this->exec('RELEASE SAVEPOINT ' . $savepoint);
            $this->transactionDepth--;
        }

        return $rolledBack;
    }

    public function inTransaction(): bool
    {
        return $this->transactionDepth > 0;
    }

    public function transactionDepth(): int
    {
        return $this->transactionDepth;
    }

    private function savepointName(int $depth): string
    {
        return 'REGRESSION_SP_' . $depth;
    }
}
