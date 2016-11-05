<?php
//
//  Copyright © 2005-2008, 2015-2016 by ale5000
//  This file is part of Skulls! Multi-Network WebCache.
//
//  Skulls is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Skulls is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Skulls.  If not, see <http://www.gnu.org/licenses/>.
//

header($_SERVER['SERVER_PROTOCOL'].' 200 OK'); list(,$prot_ver) = explode('/', $_SERVER['SERVER_PROTOCOL'], 2);
header('Content-Type: text/plain');
if($prot_ver >= 1.1) header('Cache-Control: no-cache'); else header('Pragma: no-cache');

echo "\r\n";
?>