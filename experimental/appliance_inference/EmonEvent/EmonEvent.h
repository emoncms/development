/*
  EmonEvent.h - Library for openenergymonitor
  Created by Trystan Lea, August 2  2010
  Released into the public domain.
*/

#ifndef EmonEvent_h
#define EmonEvent_h

#include "WProgram.h"

class EmonEvent
{
  public:

  EmonEvent(int _windowWidth, double _changeThreshold);

  int getState(double value);

  double slope;
  double average;
   
  private:

   double levelPoint[5];

   int windowWidth;
   double changeThreshold;
};

#endif
