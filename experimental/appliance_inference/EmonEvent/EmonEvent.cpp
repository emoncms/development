/*
  EmonEvent.cpp - Library for openenergymonitor
  Created by Trystan Lea, August 2 2010
  Released into the public domain.
*/

#include "WProgram.h"
#include "EmonEvent.h"

EmonEvent::EmonEvent(int _windowWidth, double _changeThreshold)
{
  windowWidth = _windowWidth;
  changeThreshold = _changeThreshold;
}

int EmonEvent::getState(double value)
{
  int state=0;

  levelPoint[1] = levelPoint[2];
  levelPoint[2] = levelPoint[3];
  levelPoint[3] = levelPoint[4];
  levelPoint[4] = value;
    
  //Calculate slope ----------------------------------------------
  double sumXY=0,sumX=0,sumY=0,sumX2=0;
  for (int i=1; i<(windowWidth+1); i++)
  {
    sumXY = sumXY + (i * levelPoint[i]);
    sumX = sumX + i;
    sumY = sumY + levelPoint[i]; 
    sumX2 = sumX2 +(i*i);
  }
  slope = ((windowWidth*sumXY) - (sumX*sumY))/((windowWidth*sumX2) - (sumX*sumX));
  
  average = sumY/windowWidth;

  //Detect if we are stable or changing.
  if (slope>-changeThreshold && slope<changeThreshold) state = 0; else state = 1;
  
  return state;

}
