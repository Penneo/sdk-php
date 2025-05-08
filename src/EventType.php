<?php

namespace Penneo\SDK;

enum EventType: string
{
    case CaseFileCompleted = "sign.casefile.completed";
    case CaseFileExpired = "sign.casefile.expired";
    case CaseFileFailed = "sign.casefile.failed";
    case CaseFileRejected = "sign.casefile.rejected";
    case SignerRequestSent = "sign.signer.requestSent";
    case SignerRequestOpened = "sign.signer.requestOpened";
    case SignerOpened = "sign.signer.opened";
    case SignerSigned = "sign.signer.signed";
    case SignerRejected = "sign.signer.rejected";
    case SignerReminderSent = "sign.signer.reminderSent";
    case SignerUndeliverable = "sign.signer.undeliverable";
    case SignerRequestActivated = "sign.signer.requestActivated";
    case SignerFinalized = "sign.signer.finalized";
    case SignerDeleted = "sign.signer.deleted";
    case SignerSignedWithImageUploadAndNAP = "sign.signer.signedWithImageUploadAndNAP";
    case SignerTransientBounce = "sign.signer.transientBounce";
    case WebhookSubscriptionTest = "webhook.subscription.test";
}
