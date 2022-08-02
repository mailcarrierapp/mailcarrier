<?php

namespace MailCarrier\Enums;

enum AttachmentLogStrategy: string
{
    case None = 'NONE';
    case Inline = 'INLINE';
    case Upload = 'UPLOAD';
}
