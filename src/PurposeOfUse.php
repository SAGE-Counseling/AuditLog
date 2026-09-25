<?php

namespace SageCounseling\AuditLog;

/**
 * A documented subset of the HL7/ASTM v3-PurposeOfUse value set (used in IHE ATNA
 * health-information-exchange audit logging), rather than a bespoke enum. Room to add
 * further standard values as a consuming app's other user classes need them.
 */
enum PurposeOfUse: string
{
    case Treatment = 'TREAT';
    case HealthcareOperations = 'HOPERAT';
    case Legal = 'LEGAL';
    case SystemAdministration = 'SYSADMN';
}
