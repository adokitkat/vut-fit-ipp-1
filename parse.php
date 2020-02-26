<?php
  # Container for opcodes
  $op = [
    'opcode'    => '', 'arg_count' =>  0,
    'arg1'      => '', 'arg1_type' => '',
    'arg2'      => '', 'arg2_type' => '',
    'arg3'      => '', 'arg3_type' => ''
  ];

  #Functions
  function check_instruction(&$op) {
    switch (strtoupper($op['opcode'])) {    
      // no parameter
      case 'CREATEFRAME':
      case 'PUSHFRAME':
      case 'POPFRAME':
      case 'RETURN':
      case 'BREAK':
        if ($op['arg_count'] !== 0) exit(23);
        return;

      // label
      case 'CALL':
      case 'LABEL':
      case 'JUMP':
        if ($op['arg_count'] !== 1 or $op['arg1_type'] !== 'label') exit(23);
        return;
      
      // var
      case 'DEFVAR':
      case 'POPS':
        if ($op['arg_count'] !== 1 or $op['arg1_type'] !== 'var') exit(23);
        break;
      
      // symb
      case 'PUSHS':
      case 'WRITE':
      case 'EXIT':
      case 'DPRINT':
        if (  $op['arg_count'] !== 1      or ($op['arg1_type'] !== 'var'    and $op['arg1_type'] !== 'int'
          and $op['arg1_type'] !== 'bool' and $op['arg1_type'] !== 'string' and $op['arg1_type'] !== 'nil'))
          exit(23);
        break;
      
      // var + symb
      case 'MOVE':
      case 'NOT':
      case 'INT2CHAR':
      case 'STRLEN':
      case 'TYPE':
        if (  $op['arg_count'] !== 2      or ($op['arg1_type'] !== 'var'
          and $op['arg2_type'] !== 'var'  and $op['arg2_type'] !== 'int'
          and $op['arg2_type'] !== 'bool' and $op['arg2_type'] !== 'string' and $op['arg2_type'] !== 'nil'))
          exit(23);
        break; 

      // var + type
      case 'READ':
        if (  $op['arg_count'] !== 2     or ($op['arg1_type'] !== 'var'  and $op['arg2']      !== ''
          and $op['arg2_type'] !== 'int' and $op['arg2_type'] !== 'bool' and $op['arg2_type'] !== 'string'))
          exit(23);
        break;

      // var + symb1 + symb2
      case 'ADD':
      case 'SUB':
      case 'MUL':
      case 'IDIV':
      case 'LT':
      case 'GT':
      case 'EQ':
      case 'AND':
      case 'OR':
      case 'STRI2INT':
      case 'CONCAT':
      case 'GETCHAR':
      case 'SETCHAR':
        if ( $op['arg_count'] !== 3       or ($op['arg1_type'] !== 'var'
          and $op['arg2_type'] !== 'var'  and $op['arg2_type'] !== 'int'
          and $op['arg2_type'] !== 'bool' and $op['arg2_type'] !== 'string' and $op['arg2_type'] !== 'nil'
          and $op['arg3_type'] !== 'var'  and $op['arg3_type'] !== 'int'
          and $op['arg3_type'] !== 'bool' and $op['arg3_type'] !== 'string' and $op['arg3_type'] !== 'nil'))
          exit(23);
        break;

      // label + symb1 + symb2
      case 'JUMPIFEQ':
      case 'JUMPIFNEQ':
        if (  $op['arg_count'] !== 3      or ($op['arg1_type'] !== 'label'
          and $op['arg2_type'] !== 'var'  and $op['arg2_type'] !== 'int'
          and $op['arg2_type'] !== 'bool' and $op['arg2_type'] !== 'string' and $op['arg2_type'] !== 'nil'
          and $op['arg3_type'] !== 'var'  and $op['arg3_type'] !== 'int'
          and $op['arg3_type'] !== 'bool' and $op['arg3_type'] !== 'string' and $op['arg3_type'] !== 'nil'))
          exit(23);
        break;

      default:
        exit(22);
    }

    $matches; $re_string = '/^(?!.*(\\\\\d\d\D|\\\\\d\d$|\\\\\d\D|\\\\\d$|\\\\\D|\\\\$|\s)).*$/m';
    
    for ($i = 1; $i <= $op['arg_count']; $i++) {
      if ( ($op["arg$i".'_type'] === 'int'    and !(preg_match('/^\d+$/m', $op["arg$i"], $matches, PREG_OFFSET_CAPTURE, 0)) )
        or ($op["arg$i".'_type'] === 'bool'   and $op["arg$i"] !== 'true' and $op["arg$i"] !== 'false')
        or ($op["arg$i".'_type'] === 'nil'    and $op["arg$i"] !== 'nil')
        or ($op["arg$i".'_type'] === 'string' and !(preg_match($re_string, $op["arg$i"], $matches, PREG_OFFSET_CAPTURE, 0)) )) {
        exit(23);
      }
    }    
  }

  function save_arg(&$op, $words, $i) {
    if (($type = strtoupper(strstr($words[$i], '@', true))) == 'GF' or $type == 'TF' or $type == 'LF') {
      $op["arg$i"]         = $words[$i];
      $op["arg$i".'_type'] = 'var';
    } elseif ($type == '') {
      $op["arg$i"]         = $words[$i];
      $op["arg$i".'_type'] = 'label';
    } else {
      $op["arg$i".'_type'] = strstr($words[$i], '@', true);
      $op["arg$i"] = substr(strstr($words[$i], '@'), 1);
    }
  }

  function op(&$op, $line) {
    $op['opcode'] = '';
    $op['arg1']   = ''; $op['arg1_type'] = '';
    $op['arg2']   = ''; $op['arg2_type'] = '';
    $op['arg3']   = ''; $op['arg3_type'] = '';

    if ($line_stripped = strstr($line, '#', true)) $line = $line_stripped;
    $words = preg_split('/\s+/', $line);
    $words = array_filter(array_map('trim', $words));
    
    $op['arg_count'] = count($words)-1;

    switch ($op['arg_count']) {
      case 0:
        $op['opcode'] = $words[0];
        break;

      case 1:
        $op['opcode'] = $words[0];
        save_arg($op, $words, 1);
        break;

      case 2:
        $op['opcode'] = $words[0];
        save_arg($op, $words, 1);
        save_arg($op, $words, 2);
        break;

      case 3:
        $op['opcode'] = $words[0];
        save_arg($op, $words, 1);
        save_arg($op, $words, 2);
        save_arg($op, $words, 3);
        break;
      
      default:
        exit(23);
    }
    
    check_instruction($op);
  }
  
  function order() {
    static $order = 0;
    $order++;
    return $order;
  }

  function print_arg($xw, $op, $i) {
    xmlwriter_start_element($xw, "arg$i");
    xmlwriter_start_attribute($xw, 'type');
    xmlwriter_text($xw, $op["arg$i".'_type']);
    xmlwriter_end_attribute($xw);
    xmlwriter_text($xw, $op["arg$i"]);
    xmlwriter_end_element($xw);
  }

  function print_i($xw, $op) {
    xmlwriter_start_element($xw, 'instruction');
    xmlwriter_start_attribute($xw, 'order');
    xmlwriter_text($xw, order());
    xmlwriter_end_attribute($xw);
    xmlwriter_start_attribute($xw, 'opcode');
    xmlwriter_text($xw, strtoupper($op['opcode']));
    xmlwriter_end_attribute($xw);

    if ($op['arg_count'] == 1) {
      print_arg($xw, $op, 1);
    } elseif ($op['arg_count'] == 2) {
      print_arg($xw, $op, 1);
      print_arg($xw, $op, 2);
    } elseif ($op['arg_count'] == 3) {
      print_arg($xw, $op, 1);
      print_arg($xw, $op, 2);
      print_arg($xw, $op, 3);
    }
    xmlwriter_end_element($xw);
  }

  # Main start
  // --help
  if ($argc > 1 and $argv[1] != "--help")
    exit(10);
  if ($argc == 2 and $argv[1] == "--help") {
    echo "Help..." . PHP_EOL . " please.";
    exit(0);
  }

  // Has to start with ".IPPcode20" and check for commentars
  while (!feof(STDIN)) {
    $start = trim(str_replace(array("\r", "\n"), '', fgets(STDIN)));
    if (strtolower($start) == '.ippcode20')
      break;
    elseif ($start == '' or $start[0] == '#')
      continue;
    else
      exit(21);
  }

  // Empty STDIN/file
  if (feof(STDIN)) exit(11); // ?

  // Load content from STDIN
  $content = [];
  while (!feof(STDIN)) {
    $line = trim(str_replace(array("\r", "\n"), '', fgets(STDIN)));
    if ($line == '' or $line[0] == '#')
      continue;
    op($op, $line);
    $content[] = $op;
  }

  // Print XML
  $xw = xmlwriter_open_memory();
  xmlwriter_set_indent($xw, 1);
  $res = xmlwriter_set_indent_string($xw, ' ');
  xmlwriter_start_document($xw, '1.0', 'UTF-8');
    xmlwriter_start_element($xw, 'program');
    xmlwriter_start_attribute($xw, 'language');
    xmlwriter_text($xw, 'IPPcode20');
      foreach ($content as $instruction)
        print_i($xw, $instruction);
    xmlwriter_end_element($xw);
  xmlwriter_end_document($xw);
  echo xmlwriter_output_memory($xw);

  exit(0);
?>
