<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Services_Twilio_Twiml;

class IvrController extends Controller
{
    public function __construct()
    {
        $this->_thankYouMessage = 'Thank you for calling the ET Phone Home' .
                                  ' Service - the adventurous alien\'s first choice' .
                                  ' in intergalactic travel.';
    }

    /**
     * Redirect any request with Digits=* (star) to home menu
     *
     * @return \Illuminate\Http\Response
     */
    public function checkForStar($route, $request)
    {
        if ($request->input('Digits') === '*') {
            return redirect()->route('welcome');
        }
    }

    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function showWelcome(Request $request)
    {
        if ($request->input('Digits') === '*') {
            return redirect()->route('welcome');
        } else {
            $response = new Services_Twilio_Twiml;
            $gather = $response->gather(
                ['numDigits' => 1,
                 'action' => 'https://enigmatic-castle-81076.herokuapp.com/index.php/ivr/menu-response']
            );

            $gather->play(
                'https://dl.dropboxusercontent.com/s/5zorjps57w45sre/roy_intro.mp3',
                ['loop' => 3]
            );

            return $response;
        }
    }

    /**
     * Responds to selection of an option by the caller
     *
     * @return \Illuminate\Http\Response
     */
    public function showMenuResponse(Request $request)
    {
      $response = new Services_Twilio_Twiml;

      $optionActions = [

              '1' => $response->play(

                'https://dl.dropboxusercontent.com/s/xziyksbvk9xoodh/roy_black.mp3',

                ['loop' => 1]

              ),

              '2' => $response->play(

                'https://dl.dropboxusercontent.com/s/52tkolq6nkzgg99/roy_spanish.mp3',

                ['loop' => 1]

              ),

              '3' => $response->play(

                'https://dl.dropboxusercontent.com/s/8ln8yw4wif56yo1/roy_white.mp3',

                ['loop' => 1]

              ),

              '4' => $response->play(

                'https://dl.dropboxusercontent.com/s/p19thti9tb5mqch/roy_other.mp3',

                ['loop' => 1]

              )

          ];
        $selectedOption = $request->input('Digits');

        $actionExists = isset($optionActions[$selectedOption]);

        if ($actionExists) {
            $selectedAction = $optionActions[$selectedOption];
            error_log($selectedAction);
               return "<Response>".$selectedAction."<Redirect>https://enigmatic-castle-81076.herokuapp.com/index.php/ivr/welcome</Redirect></Response>";

        } else {
            $response = new Services_Twilio_Twiml;
            $response->say(
                'Returning to the main menu',
                ['voice' => 'Alice', 'language' => 'en-GB']
            );
            $response->redirect('https://enigmatic-castle-81076.herokuapp.com/index.php/ivr/welcome');

            return $response;
        }

    }

    /**
     * Responds with a <Dial> to the caller's planet
     *
     * @return \Illuminate\Http\Response
     */
    public function showPlanetConnection(Request $request)
    {
        $response = new Services_Twilio_Twiml;
        $response->say(
            $this->_thankYouMessage,
            ['voice' => 'Alice', 'language' => 'en-GB']
        );
        $response->say(
            "You'll be connected shortly to your planet",
            ['voice' => 'Alice', 'language' => 'en-GB']
        );

        $planetNumbers = [
            '2' => '+12024173378',
            '3' => '+12027336386',
            '4' => '+12027336637'
        ];
        $selectedOption = $request->input('Digits');

        $planetNumberExists = isset($planetNumbers[$selectedOption]);

        if ($planetNumberExists) {
            $selectedNumber = $planetNumbers[$selectedOption];
            $response->dial($selectedNumber);

            return $response;
        } else {
            $errorResponse = new Services_Twilio_Twiml;
            $errorResponse->say(
                'Returning to the main menu',
                ['voice' => 'Alice', 'language' => 'en-GB']
            );
            $errorResponse->redirect(route('welcome', [], false));

            return $errorResponse;
        }

    }


    /**
     * Responds with instructions to mothership
     * @return Services_Twilio_Twiml
     */
    private function _getReturnInstructions()
    {
        $response = new Services_Twilio_Twiml;
        $response->say(
            'To get to your extraction point, get on your bike and go down the' .
            ' street. Then Left down an alley. Avoid the police cars. Turn left' .
            ' into an unfinished housing development. Fly over the roadblock. Go' .
            ' passed the moon. Soon after you will see your mother ship.',
            ['voice' => 'Alice', 'language' => 'en-GB']
        );
        $response->say(
            $this->_thankYouMessage,
            ['voice' => 'Alice', 'language' => 'en-GB']
        );

        $response->hangup();

        return $response;
    }

    /**
     * Responds with instructions to choose a planet
     * @return Services_Twilio_Twiml
     */
    private function _getPlanetsMenu()
    {
        $response = new Services_Twilio_Twiml;
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('planet-connection', [], false)]
        );
        $gather->say(
            'To call the planet Brodo Asogi, press 2. To call the planet' .
            ' Dugobah, press 3. To call an Oober asteroid to your location,' .
            ' press 4. To go back to the main menu, press the star key',
            ['voice' => 'Alice', 'language' => 'en-GB']
        );

        return $response;
    }
}
