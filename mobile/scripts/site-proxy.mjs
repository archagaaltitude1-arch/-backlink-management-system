import http from 'node:http';

const port = Number(process.env.SITE_PROXY_PORT || 8090);
const targetPort = Number(process.env.SITE_PROXY_TARGET || 8000);

const server = http.createServer((request, response) => {
    const proxy = http.request({
        hostname: '127.0.0.1',
        port: targetPort,
        path: request.url,
        method: request.method,
        headers: request.headers,
    }, (upstream) => {
        response.writeHead(upstream.statusCode || 502, upstream.headers);
        upstream.pipe(response);
    });

    proxy.on('error', () => {
        if (!response.headersSent) {
            response.writeHead(502, { 'Content-Type': 'text/plain' });
        }
        response.end('The website is not running.');
    });

    request.pipe(proxy);
});

server.listen(port, '0.0.0.0', () => {
    console.log(`Site proxy listening on 0.0.0.0:${port} -> 127.0.0.1:${targetPort}`);
});
