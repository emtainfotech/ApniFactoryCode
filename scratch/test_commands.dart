import 'package:flutter_tools/executable.dart' as executable;

void main() {
  print('Generating commands...');
  final sw = Stopwatch()..start();
  final cmds = executable.generateCommands(verboseHelp: false, verbose: false);
  print('Generated ${cmds.length} commands in ${sw.elapsedMilliseconds}ms');
}

