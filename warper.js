const spawn = require('child_process').spawn;
const ls = spawn('php', ['worker.php']);

console.log('Worker with node.js')

ls.stdout.on('data', (data) => {
 console.log(data);
});

ls.stderr.on('data', (data) => {
 console.log(`stderr: ${data}`);
});

ls.on('close', (code) => {
 console.log(`child process exited with code ${code}`);
});