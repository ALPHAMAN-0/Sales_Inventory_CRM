<?php

namespace App\Domain\Crm\Enums;

/**
 * Customer lifecycle state machine:
 *   Active --(no purchase in N days)--> Lost
 *   Lost   --(purchases w/ open assignment)--> Recovered
 *   Lost   --(purchases w/o assignment)--> Active
 *   Recovered --(normal activity)--> Active
 */
enum CustomerStatus: string
{
    case Active = 'active';
    case Lost = 'lost';
    case Recovered = 'recovered';
}
