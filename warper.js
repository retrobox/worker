console.log('Starting worker with node.js...')

const spawn = require('child_process').spawn;
const ls = spawn('php', ['worker.php']);

ls.stdout.on('data', (data) => {
 console.log(data.toString());
});

ls.stderr.on('data', (data) => {
 console.log(`stderr: ${data}`);
});

ls.on('close', (code) => {
 console.log(`child process exited with code ${code}`);
});
