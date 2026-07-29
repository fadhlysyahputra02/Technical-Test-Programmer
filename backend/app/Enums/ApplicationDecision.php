<?php

namespace App\Enums;

enum ApplicationDecision: string
{
    case RevisionRequested = 'revision_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
