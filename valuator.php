<?php
//fields
$p = array
  (
  //this is from http://www.gummy-stuff.org/Yahoo-data.htm
      'ticker'    => 's',
      'name'      => 'n',
      'cap'       => 'j1',
      'pe'        => 'r0',
      'f_pe'      => 'r7',
      'peg'       => 'r5',
      'p_s'       => 'p5',
      'div_y'     => 'y',
      '50_avg'    => 'm3',
      '200_avg'   => 'm4',
      'div_date'  => 'r1',
      'last_p'    => 'l1',
      'target'    => 't8',
     // 'after_h'         => 'c8',
      'change'    => 'k2',
      'error'     => 'e1',

  );

//this function returns an array with data
function get_stocks($stocks)
{
  global $p;
  $company = array(); //will store companies here
  $stock = '';
  //combine the link from stocks and fields 
  $n=0;
  foreach($stocks as $k=>$v) { if ($n>0) { $stock .= '+'; }  $stock .= $v; $n++; }
  $st = 'http://download.finance.yahoo.com/d/quotes.csv?s=' . $stock . '&f=';

  //make a url to query 
  foreach ($p as $key=>$value) { $st .= $value; }

  //array of labels in order
  $lbl = array();
  foreach ($p as $key=>$value) { $lbl[] = $key; }

  //start streaming
  $stream = fopen($st, 'r');
  $line = fgetcsv($stream);
  while ($line)
  {
    foreach($line as $k=>$v)
    {
      $company[$line[0]][$lbl[$k]] = $v; // $company[ticker][label] = value
    }
    $line = fgetcsv($stream);
  }
  fclose($stream);

  return $company;
}

// a score of an individual company
function score($data)
{
  $score = 0;

  //trailing PE
  if ($data['pe'] == 'N/A') { $score += 0; }
  else if ($data['pe'] < 11) { $score += 5; }
  else if ($data['pe'] < 15) { $score += 4; }
  else if ($data['pe'] < 32) { $score += 3; }
  else if ($data['pe'] < 80) { $score += 1; }

  //forward_pe
  if ($data['f_pe'] < 11) { $score += 5; }
  else if ($data['f_pe'] < 15) { $score += 4; }
  else if ($data['f_pe'] < 32) { $score += 3; }
  else if ($data['f_pe'] < 80) { $score += 1; }

  // PEG
  if ($data['peg'] == 'N/A') { $score += 0; }
  else if ($data['peg'] < 1) { $score += 5; }
  else if ($data['peg'] < 1.5) { $score += 4; }
  else if ($data['peg'] < 2) { $score += 3; }
  else if ($data['peg'] < 3) { $score += 1; }

  //price-sales
  if ($data['p_s'] < 1) { $score += 5; }
  else if ($data['p_s'] < 2) { $score += 4; }
  else if ($data['p_s'] < 5) { $score += 3; }
  else if ($data['p_s'] < 9) { $score += 1; }

  //divident yeld
  if ($data['div_y'] > 8) { $score += 5; }
  else if ($data['div_y'] > 4) { $score += 4; }
  else if ($data['div_y'] < 2) { $score += 3; }
  else if ($data['div_y'] > 1) { $score += 1; }

  //Moving avarages              
  if ($data['last_p'] > $data['50_avg']) { $score += 1; } //upwords trend
  if ($data['50_avg'] > $data['200_avg']) { $score += 1; }
  //http://ichart.finance.yahoo.com/table.csv?s=YHOO&a=03&b=12&c=1996&d=11&e=2&f=2005&g=d&ignore=.csv                          

  if ($score < 0) { $score = 0; } //no peg or pe
  return $score;
}


function valued_stocks($stocks)
{
  $rez = get_stocks($stocks);

  foreach ($rez as $tik => $lbl)
  {
    $score = score($rez[$tik]);
    $rez[$tik]['score'] = $score;
  }
  return $rez;
}                                                                                                                                                                                                                                 119,1         Bot

