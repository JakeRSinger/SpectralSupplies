<?php
session_start();
session_unset();
session_destroy();

header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/index.html");