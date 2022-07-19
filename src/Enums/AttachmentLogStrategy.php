<?php

namespace MailCarrier\MailCarrier\Enums;

enum AttachmentLogStrategy: string
{
    case None = 'NONE';
    case Inline = 'INLINE';
    case Upload = 'UPLOAD';
}
