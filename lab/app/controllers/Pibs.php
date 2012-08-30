<?php

class Lab_Pibs_Controller extends Horde_Controller_Base
{
    public function processRequest(Horde_Controller_Request $request, Horde_Controller_Response $response)
    {
        $requestVars = $request->getPath();
echo $requestVars;
        if (isset($requestVars['id'])) {
            switch ($request->getMethod()) {
            case 'DELETE': //delete

                break;
            case 'POST': //append, update
                break;
            case 'PUT': //create, overwrite

                break;
            default: // get, show
                echo "Show single pib";
                break;
            }
        } else { //index
            echo "Display INdex";
        }
    }
}
