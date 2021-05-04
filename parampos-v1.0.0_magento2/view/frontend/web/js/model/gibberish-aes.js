!function (a, b) {
    a.GibberishAES = b()
}(this, function () {
    "use strict";
    var a = 14, b = 8, c = !1, d = function (a) {
            try {
                return unescape(encodeURIComponent(a))
            } catch (a) {
                throw"Error on UTF-8 encode"
            }
        }, e = function (a) {
            try {
                return decodeURIComponent(escape(a))
            } catch (a) {
                throw"Bad Key"
            }
        }, f = function (a) {
            var c, d, b = [];
            for (a.length < 16 && (c = 16 - a.length, b = [c, c, c, c, c, c, c, c, c, c, c, c, c, c, c, c]), d = 0; d < a.length; d++) b[d] = a[d];
            return b
        }, g = function (a, b) {
            var d, e, c = "";
            if (b) {
                if (d = a[15], d > 16) throw"Decryption error: Maybe bad key";
                if (16 === d) return "";
                for (e = 0; e < 16 - d; e++) c += String.fromCharCode(a[e])
            } else for (e = 0; e < 16; e++) c += String.fromCharCode(a[e]);
            return c
        }, h = function (a) {
            var c, b = "";
            for (c = 0; c < a.length; c++) b += (a[c] < 16 ? "0" : "") + a[c].toString(16);
            return b
        }, i = function (a) {
            var b = [];
            return a.replace(/(..)/g, function (a) {
                b.push(parseInt(a, 16))
            }), b
        }, j = function (a, b) {
            var e, c = [];
            for (b || (a = d(a)), e = 0; e < a.length; e++) c[e] = a.charCodeAt(e);
            return c
        }, k = function (c) {
            switch (c) {
                case 128:
                    a = 10, b = 4;
                    break;
                case 192:
                    a = 12, b = 6;
                    break;
                case 256:
                    a = 14, b = 8;
                    break;
                default:
                    throw"Invalid Key Size Specified:" + c
            }
        }, l = function (a) {
            var c, b = [];
            for (c = 0; c < a; c++) b = b.concat(Math.floor(256 * Math.random()));
            return b
        }, m = function (c, d) {
            var k, e = a >= 12 ? 3 : 2, f = [], g = [], h = [], i = [], j = c.concat(d);
            for (h[0] = O(j), i = h[0], k = 1; k < e; k++) h[k] = O(h[k - 1].concat(j)), i = i.concat(h[k]);
            return f = i.slice(0, 4 * b), g = i.slice(4 * b, 4 * b + 16), {key: f, iv: g}
        }, n = function (a, b, c) {
            b = w(b);
            var g, d = Math.ceil(a.length / 16), e = [], h = [];
            for (g = 0; g < d; g++) e[g] = f(a.slice(16 * g, 16 * g + 16));
            for (a.length % 16 === 0 && (e.push([16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16]), d++), g = 0; g < e.length; g++) e[g] = 0 === g ? v(e[g], c) : v(e[g], h[g - 1]), h[g] = p(e[g], b);
            return h
        }, o = function (a, b, c, d) {
            b = w(b);
            var i, f = a.length / 16, h = [], j = [], k = "";
            for (i = 0; i < f; i++) h.push(a.slice(16 * i, 16 * (i + 1)));
            for (i = h.length - 1; i >= 0; i--) j[i] = q(h[i], b), j[i] = 0 === i ? v(j[i], c) : v(j[i], h[i - 1]);
            for (i = 0; i < f - 1; i++) k += g(j[i]);
            return k += g(j[i], !0), d ? k : e(k)
        }, p = function (b, d) {
            c = !1;
            var f, e = u(b, d, 0);
            for (f = 1; f < a + 1; f++) e = r(e), e = s(e), f < a && (e = t(e)), e = u(e, d, f);
            return e
        }, q = function (b, d) {
            c = !0;
            var f, e = u(b, d, a);
            for (f = a - 1; f > -1; f--) e = s(e), e = r(e), e = u(e, d, f), f > 0 && (e = t(e));
            return e
        }, r = function (a) {
            var e, b = c ? E : D, d = [];
            for (e = 0; e < 16; e++) d[e] = b[a[e]];
            return d
        }, s = function (a) {
            var e, b = [],
                d = c ? [0, 13, 10, 7, 4, 1, 14, 11, 8, 5, 2, 15, 12, 9, 6, 3] : [0, 5, 10, 15, 4, 9, 14, 3, 8, 13, 2, 7, 12, 1, 6, 11];
            for (e = 0; e < 16; e++) b[e] = a[d[e]];
            return b
        }, t = function (a) {
            var d, b = [];
            if (c) for (d = 0; d < 4; d++) b[4 * d] = L[a[4 * d]] ^ J[a[1 + 4 * d]] ^ K[a[2 + 4 * d]] ^ I[a[3 + 4 * d]], b[1 + 4 * d] = I[a[4 * d]] ^ L[a[1 + 4 * d]] ^ J[a[2 + 4 * d]] ^ K[a[3 + 4 * d]], b[2 + 4 * d] = K[a[4 * d]] ^ I[a[1 + 4 * d]] ^ L[a[2 + 4 * d]] ^ J[a[3 + 4 * d]], b[3 + 4 * d] = J[a[4 * d]] ^ K[a[1 + 4 * d]] ^ I[a[2 + 4 * d]] ^ L[a[3 + 4 * d]]; else for (d = 0; d < 4; d++) b[4 * d] = G[a[4 * d]] ^ H[a[1 + 4 * d]] ^ a[2 + 4 * d] ^ a[3 + 4 * d], b[1 + 4 * d] = a[4 * d] ^ G[a[1 + 4 * d]] ^ H[a[2 + 4 * d]] ^ a[3 + 4 * d], b[2 + 4 * d] = a[4 * d] ^ a[1 + 4 * d] ^ G[a[2 + 4 * d]] ^ H[a[3 + 4 * d]], b[3 + 4 * d] = H[a[4 * d]] ^ a[1 + 4 * d] ^ a[2 + 4 * d] ^ G[a[3 + 4 * d]];
            return b
        }, u = function (a, b, c) {
            var e, d = [];
            for (e = 0; e < 16; e++) d[e] = a[e] ^ b[c][e];
            return d
        }, v = function (a, b) {
            var d, c = [];
            for (d = 0; d < 16; d++) c[d] = a[d] ^ b[d];
            return c
        }, w = function (c) {
            var f, g, h, j, d = [], e = [], i = [];
            for (f = 0; f < b; f++) g = [c[4 * f], c[4 * f + 1], c[4 * f + 2], c[4 * f + 3]], d[f] = g;
            for (f = b; f < 4 * (a + 1); f++) {
                for (d[f] = [], h = 0; h < 4; h++) e[h] = d[f - 1][h];
                for (f % b === 0 ? (e = x(y(e)), e[0] ^= F[f / b - 1]) : b > 6 && f % b === 4 && (e = x(e)), h = 0; h < 4; h++) d[f][h] = d[f - b][h] ^ e[h]
            }
            for (f = 0; f < a + 1; f++) for (i[f] = [], j = 0; j < 4; j++) i[f].push(d[4 * f + j][0], d[4 * f + j][1], d[4 * f + j][2], d[4 * f + j][3]);
            return i
        }, x = function (a) {
            for (var b = 0; b < 4; b++) a[b] = D[a[b]];
            return a
        }, y = function (a) {
            var c, b = a[0];
            for (c = 0; c < 4; c++) a[c] = a[c + 1];
            return a[3] = b, a
        }, z = function (a, b) {
            var c, d = [];
            for (c = 0; c < a.length; c += b) d[c / b] = parseInt(a.substr(c, b), 16);
            return d
        }, A = function (a) {
            var b, c = [];
            for (b = 0; b < a.length; b++) c[a[b]] = b;
            return c
        }, B = function (a, b) {
            var c, d;
            for (d = 0, c = 0; c < 8; c++) d = 1 === (1 & b) ? d ^ a : d, a = a > 127 ? 283 ^ a << 1 : a << 1, b >>>= 1;
            return d
        }, C = function (a) {
            var b, c = [];
            for (b = 0; b < 256; b++) c[b] = B(a, b);
            return c
        },
        D = z("637c777bf26b6fc53001672bfed7ab76ca82c97dfa5947f0add4a2af9ca472c0b7fd9326363ff7cc34a5e5f171d8311504c723c31896059a071280e2eb27b27509832c1a1b6e5aa0523bd6b329e32f8453d100ed20fcb15b6acbbe394a4c58cfd0efaafb434d338545f9027f503c9fa851a3408f929d38f5bcb6da2110fff3d2cd0c13ec5f974417c4a77e3d645d197360814fdc222a908846eeb814de5e0bdbe0323a0a4906245cc2d3ac629195e479e7c8376d8dd54ea96c56f4ea657aae08ba78252e1ca6b4c6e8dd741f4bbd8b8a703eb5664803f60e613557b986c11d9ee1f8981169d98e949b1e87e9ce5528df8ca1890dbfe6426841992d0fb054bb16", 2),
        E = A(D), F = z("01020408102040801b366cd8ab4d9a2f5ebc63c697356ad4b37dfaefc591", 2), G = C(2), H = C(3),
        I = C(9), J = C(11), K = C(13), L = C(14), M = function (a, b, c) {
            var h, d = l(8), e = m(j(b, c), d), f = e.key, g = e.iv, i = [[83, 97, 108, 116, 101, 100, 95, 95].concat(d)];
            return a = j(a, c), h = n(a, f, g), h = i.concat(h), R.encode(h)
        }, N = function (a, b, c) {
            var d = R.decode(a), e = d.slice(8, 16), f = m(j(b, c), e), g = f.key, h = f.iv;
            return d = d.slice(16, d.length), a = o(d, g, h, c)
        }, O = function (a) {
            function b(a, b) {
                return a << b | a >>> 32 - b
            }

            function c(a, b) {
                var c, d, e, f, g;
                return e = 2147483648 & a, f = 2147483648 & b, c = 1073741824 & a, d = 1073741824 & b, g = (1073741823 & a) + (1073741823 & b), c & d ? 2147483648 ^ g ^ e ^ f : c | d ? 1073741824 & g ? 3221225472 ^ g ^ e ^ f : 1073741824 ^ g ^ e ^ f : g ^ e ^ f
            }

            function d(a, b, c) {
                return a & b | ~a & c
            }

            function e(a, b, c) {
                return a & c | b & ~c
            }

            function f(a, b, c) {
                return a ^ b ^ c
            }

            function g(a, b, c) {
                return b ^ (a | ~c)
            }

            function h(a, e, f, g, h, i, j) {
                return a = c(a, c(c(d(e, f, g), h), j)), c(b(a, i), e)
            }

            function i(a, d, f, g, h, i, j) {
                return a = c(a, c(c(e(d, f, g), h), j)), c(b(a, i), d)
            }

            function j(a, d, e, g, h, i, j) {
                return a = c(a, c(c(f(d, e, g), h), j)), c(b(a, i), d)
            }

            function k(a, d, e, f, h, i, j) {
                return a = c(a, c(c(g(d, e, f), h), j)), c(b(a, i), d)
            }

            function l(a) {
                for (var b, c = a.length, d = c + 8, e = (d - d % 64) / 64, f = 16 * (e + 1), g = [], h = 0, i = 0; i < c;) b = (i - i % 4) / 4, h = i % 4 * 8, g[b] = g[b] | a[i] << h, i++;
                return b = (i - i % 4) / 4, h = i % 4 * 8, g[b] = g[b] | 128 << h, g[f - 2] = c << 3, g[f - 1] = c >>> 29, g
            }

            function m(a) {
                var b, c, d = [];
                for (c = 0; c <= 3; c++) b = a >>> 8 * c & 255, d = d.concat(b);
                return d
            }

            var o, p, q, r, s, t, u, v, w, n = [],
                x = z("67452301efcdab8998badcfe10325476d76aa478e8c7b756242070dbc1bdceeef57c0faf4787c62aa8304613fd469501698098d88b44f7afffff5bb1895cd7be6b901122fd987193a679438e49b40821f61e2562c040b340265e5a51e9b6c7aad62f105d02441453d8a1e681e7d3fbc821e1cde6c33707d6f4d50d87455a14eda9e3e905fcefa3f8676f02d98d2a4c8afffa39428771f6816d9d6122fde5380ca4beea444bdecfa9f6bb4b60bebfbc70289b7ec6eaa127fad4ef308504881d05d9d4d039e6db99e51fa27cf8c4ac5665f4292244432aff97ab9423a7fc93a039655b59c38f0ccc92ffeff47d85845dd16fa87e4ffe2ce6e0a30143144e0811a1f7537e82bd3af2352ad7d2bbeb86d391", 8);
            for (n = l(a), t = x[0], u = x[1], v = x[2], w = x[3], o = 0; o < n.length; o += 16) p = t, q = u, r = v, s = w, t = h(t, u, v, w, n[o + 0], 7, x[4]), w = h(w, t, u, v, n[o + 1], 12, x[5]), v = h(v, w, t, u, n[o + 2], 17, x[6]), u = h(u, v, w, t, n[o + 3], 22, x[7]), t = h(t, u, v, w, n[o + 4], 7, x[8]), w = h(w, t, u, v, n[o + 5], 12, x[9]), v = h(v, w, t, u, n[o + 6], 17, x[10]), u = h(u, v, w, t, n[o + 7], 22, x[11]), t = h(t, u, v, w, n[o + 8], 7, x[12]), w = h(w, t, u, v, n[o + 9], 12, x[13]), v = h(v, w, t, u, n[o + 10], 17, x[14]), u = h(u, v, w, t, n[o + 11], 22, x[15]), t = h(t, u, v, w, n[o + 12], 7, x[16]), w = h(w, t, u, v, n[o + 13], 12, x[17]), v = h(v, w, t, u, n[o + 14], 17, x[18]), u = h(u, v, w, t, n[o + 15], 22, x[19]), t = i(t, u, v, w, n[o + 1], 5, x[20]), w = i(w, t, u, v, n[o + 6], 9, x[21]), v = i(v, w, t, u, n[o + 11], 14, x[22]), u = i(u, v, w, t, n[o + 0], 20, x[23]), t = i(t, u, v, w, n[o + 5], 5, x[24]), w = i(w, t, u, v, n[o + 10], 9, x[25]), v = i(v, w, t, u, n[o + 15], 14, x[26]), u = i(u, v, w, t, n[o + 4], 20, x[27]), t = i(t, u, v, w, n[o + 9], 5, x[28]), w = i(w, t, u, v, n[o + 14], 9, x[29]), v = i(v, w, t, u, n[o + 3], 14, x[30]), u = i(u, v, w, t, n[o + 8], 20, x[31]), t = i(t, u, v, w, n[o + 13], 5, x[32]), w = i(w, t, u, v, n[o + 2], 9, x[33]), v = i(v, w, t, u, n[o + 7], 14, x[34]), u = i(u, v, w, t, n[o + 12], 20, x[35]), t = j(t, u, v, w, n[o + 5], 4, x[36]), w = j(w, t, u, v, n[o + 8], 11, x[37]), v = j(v, w, t, u, n[o + 11], 16, x[38]), u = j(u, v, w, t, n[o + 14], 23, x[39]), t = j(t, u, v, w, n[o + 1], 4, x[40]), w = j(w, t, u, v, n[o + 4], 11, x[41]), v = j(v, w, t, u, n[o + 7], 16, x[42]), u = j(u, v, w, t, n[o + 10], 23, x[43]), t = j(t, u, v, w, n[o + 13], 4, x[44]), w = j(w, t, u, v, n[o + 0], 11, x[45]), v = j(v, w, t, u, n[o + 3], 16, x[46]), u = j(u, v, w, t, n[o + 6], 23, x[47]), t = j(t, u, v, w, n[o + 9], 4, x[48]), w = j(w, t, u, v, n[o + 12], 11, x[49]), v = j(v, w, t, u, n[o + 15], 16, x[50]), u = j(u, v, w, t, n[o + 2], 23, x[51]), t = k(t, u, v, w, n[o + 0], 6, x[52]), w = k(w, t, u, v, n[o + 7], 10, x[53]), v = k(v, w, t, u, n[o + 14], 15, x[54]), u = k(u, v, w, t, n[o + 5], 21, x[55]), t = k(t, u, v, w, n[o + 12], 6, x[56]), w = k(w, t, u, v, n[o + 3], 10, x[57]), v = k(v, w, t, u, n[o + 10], 15, x[58]), u = k(u, v, w, t, n[o + 1], 21, x[59]), t = k(t, u, v, w, n[o + 8], 6, x[60]), w = k(w, t, u, v, n[o + 15], 10, x[61]), v = k(v, w, t, u, n[o + 6], 15, x[62]), u = k(u, v, w, t, n[o + 13], 21, x[63]), t = k(t, u, v, w, n[o + 4], 6, x[64]), w = k(w, t, u, v, n[o + 11], 10, x[65]), v = k(v, w, t, u, n[o + 2], 15, x[66]), u = k(u, v, w, t, n[o + 9], 21, x[67]), t = c(t, p), u = c(u, q), v = c(v, r), w = c(w, s);
            return m(t).concat(m(u), m(v), m(w))
        }, R = function () {
            var a = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", b = a.split(""),
                c = function (a, c) {
                    var f, g, d = [], e = "";
                    Math.floor(16 * a.length / 3);
                    for (f = 0; f < 16 * a.length; f++) d.push(a[Math.floor(f / 16)][f % 16]);
                    for (f = 0; f < d.length; f += 3) e += b[d[f] >> 2], e += b[(3 & d[f]) << 4 | d[f + 1] >> 4], e += void 0 !== d[f + 1] ? b[(15 & d[f + 1]) << 2 | d[f + 2] >> 6] : "=", e += void 0 !== d[f + 2] ? b[63 & d[f + 2]] : "=";
                    for (g = e.slice(0, 64) + "\n", f = 1; f < Math.ceil(e.length / 64); f++) g += e.slice(64 * f, 64 * f + 64) + (Math.ceil(e.length / 64) === f + 1 ? "" : "\n");
                    return g
                }, d = function (b) {
                    b = b.replace(/\n/g, "");
                    var f, c = [], d = [], e = [];
                    for (f = 0; f < b.length; f += 4) d[0] = a.indexOf(b.charAt(f)), d[1] = a.indexOf(b.charAt(f + 1)), d[2] = a.indexOf(b.charAt(f + 2)), d[3] = a.indexOf(b.charAt(f + 3)), e[0] = d[0] << 2 | d[1] >> 4, e[1] = (15 & d[1]) << 4 | d[2] >> 2, e[2] = (3 & d[2]) << 6 | d[3], c.push(e[0], e[1], e[2]);
                    return c = c.slice(0, c.length - c.length % 16)
                };
            return "function" == typeof Array.indexOf && (a = b), {encode: c, decode: d}
        }();
    return {
        size: k,
        h2a: i,
        expandKey: w,
        encryptBlock: p,
        decryptBlock: q,
        Decrypt: c,
        s2a: j,
        rawEncrypt: n,
        rawDecrypt: o,
        dec: N,
        openSSLKey: m,
        a2h: h,
        enc: M,
        Hash: {MD5: O},
        Base64: R
    }
});