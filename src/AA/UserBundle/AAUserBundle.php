<?php

namespace AA\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AAUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
