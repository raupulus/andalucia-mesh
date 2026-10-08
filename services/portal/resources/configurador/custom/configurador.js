typeof window < "u" && (window.global = window.global || window, window.process = window.process || {
	env: { NODE_ENV: "production" },
	version: "",
	versions: {},
	platform: "browser",
	cwd: function() {
		return "";
	}
}, window.Buffer || (window.Buffer = {
	isBuffer: function(e) {
		return !!(e && (e._isBuffer || e.constructor && e.constructor.isBuffer && e.constructor.isBuffer(e)));
	},
	from: function(e) {
		return new Uint8Array(e);
	},
	alloc: function(e) {
		return new Uint8Array(e);
	}
}, globalThis.Buffer = window.Buffer));
//#region node_modules/@meshtastic/core/dist/chunk-DbKvDyjX.js
var e = Object.create, t = Object.defineProperty, n = Object.getOwnPropertyDescriptor, r = Object.getOwnPropertyNames, i = Object.getPrototypeOf, a = Object.prototype.hasOwnProperty, o = (e, t) => function() {
	return t || (0, e[r(e)[0]])((t = { exports: {} }).exports, t), t.exports;
}, s = (e) => {
	let n = {};
	for (var r in e) t(n, r, {
		get: e[r],
		enumerable: !0
	});
	return n;
}, c = (e, i, o, s) => {
	if (i && typeof i == "object" || typeof i == "function") for (var c = r(i), l = 0, u = c.length, d; l < u; l++) d = c[l], !a.call(e, d) && d !== o && t(e, d, {
		get: ((e) => i[e]).bind(null, d),
		enumerable: !(s = n(i, d)) || s.enumerable
	});
	return e;
}, l = (n, r, a) => (a = n == null ? {} : e(i(n)), c(r || !n || !n.__esModule ? t(a, "default", {
	value: n,
	enumerable: !0
}) : a, n));
//#endregion
//#region \0os
function u() {
	return "localhost";
}
//#endregion
//#region \0path
function d(e) {
	return String(e || "");
}
//#endregion
//#region \0util
function f(e, ...t) {
	return t.map((e) => {
		if (typeof e == "object" && e) try {
			return JSON.stringify(e);
		} catch {
			return String(e);
		}
		return String(e);
	}).join(" ");
}
var ee = { isNativeError: (e) => e instanceof Error }, p = [
	0,
	4129,
	8258,
	12387,
	16516,
	20645,
	24774,
	28903,
	33032,
	37161,
	41290,
	45419,
	49548,
	53677,
	57806,
	61935,
	4657,
	528,
	12915,
	8786,
	21173,
	17044,
	29431,
	25302,
	37689,
	33560,
	45947,
	41818,
	54205,
	50076,
	62463,
	58334,
	9314,
	13379,
	1056,
	5121,
	25830,
	29895,
	17572,
	21637,
	42346,
	46411,
	34088,
	38153,
	58862,
	62927,
	50604,
	54669,
	13907,
	9842,
	5649,
	1584,
	30423,
	26358,
	22165,
	18100,
	46939,
	42874,
	38681,
	34616,
	63455,
	59390,
	55197,
	51132,
	18628,
	22757,
	26758,
	30887,
	2112,
	6241,
	10242,
	14371,
	51660,
	55789,
	59790,
	63919,
	35144,
	39273,
	43274,
	47403,
	23285,
	19156,
	31415,
	27286,
	6769,
	2640,
	14899,
	10770,
	56317,
	52188,
	64447,
	60318,
	39801,
	35672,
	47931,
	43802,
	27814,
	31879,
	19684,
	23749,
	11298,
	15363,
	3168,
	7233,
	60846,
	64911,
	52716,
	56781,
	44330,
	48395,
	36200,
	40265,
	32407,
	28342,
	24277,
	20212,
	15891,
	11826,
	7761,
	3696,
	65439,
	61374,
	57309,
	53244,
	48923,
	44858,
	40793,
	36728,
	37256,
	33193,
	45514,
	41451,
	53516,
	49453,
	61774,
	57711,
	4224,
	161,
	12482,
	8419,
	20484,
	16421,
	28742,
	24679,
	33721,
	37784,
	41979,
	46042,
	49981,
	54044,
	58239,
	62302,
	689,
	4752,
	8947,
	13010,
	16949,
	21012,
	25207,
	29270,
	46570,
	42443,
	38312,
	34185,
	62830,
	58703,
	54572,
	50445,
	13538,
	9411,
	5280,
	1153,
	29798,
	25671,
	21540,
	17413,
	42971,
	47098,
	34713,
	38840,
	59231,
	63358,
	50973,
	55100,
	9939,
	14066,
	1681,
	5808,
	26199,
	30326,
	17941,
	22068,
	55628,
	51565,
	63758,
	59695,
	39368,
	35305,
	47498,
	43435,
	22596,
	18533,
	30726,
	26663,
	6336,
	2273,
	14466,
	10403,
	52093,
	56156,
	60223,
	64286,
	35833,
	39896,
	43963,
	48026,
	19061,
	23124,
	27191,
	31254,
	2801,
	6864,
	10931,
	14994,
	64814,
	60687,
	56684,
	52557,
	48554,
	44427,
	40424,
	36297,
	31782,
	27655,
	23652,
	19525,
	15522,
	11395,
	7392,
	3265,
	61215,
	65342,
	53085,
	57212,
	44955,
	49082,
	36825,
	40952,
	28183,
	32310,
	20053,
	24180,
	11923,
	16050,
	3793,
	7920
];
typeof Int32Array < "u" && (p = new Int32Array(p));
var te = (e, t) => {
	let n = t === void 0 ? 65535 : ~~t;
	for (let t = 0; t < e.length; t++) n = (p[(n >> 8 ^ e[t]) & 255] ^ n << 8) & 65535;
	return n;
}, ne = Object.defineProperty, m = (e) => {
	let t = {};
	for (var n in e) ne(t, n, {
		get: e[n],
		enumerable: !0
	});
	return t;
};
function re(e) {
	let t = !1, n = [];
	for (let r = 0; r < e.length; r++) {
		let i = e.charAt(r);
		switch (i) {
			case "_":
				t = !0;
				break;
			case "0":
			case "1":
			case "2":
			case "3":
			case "4":
			case "5":
			case "6":
			case "7":
			case "8":
			case "9":
				n.push(i), t = !1;
				break;
			default: t && (t = !1, i = i.toUpperCase()), n.push(i);
		}
	}
	return n.join("");
}
var ie = /* @__PURE__ */ new Set([
	"constructor",
	"toString",
	"toJSON",
	"valueOf"
]);
function ae(e) {
	return ie.has(e) ? e + "$" : e;
}
function oe() {
	let e = 0, t = 0;
	for (let n = 0; n < 28; n += 7) {
		let r = this.buf[this.pos++];
		if (e |= (r & 127) << n, !(r & 128)) return this.assertBounds(), [e, t];
	}
	let n = this.buf[this.pos++];
	if (e |= (n & 15) << 28, t = (n & 112) >> 4, !(n & 128)) return this.assertBounds(), [e, t];
	for (let n = 3; n <= 31; n += 7) {
		let r = this.buf[this.pos++];
		if (t |= (r & 127) << n, !(r & 128)) return this.assertBounds(), [e, t];
	}
	throw Error("invalid varint");
}
function se(e, t, n) {
	for (let r = 0; r < 28; r += 7) {
		let i = e >>> r, a = !(!(i >>> 7) && t == 0), o = (a ? i | 128 : i) & 255;
		if (n.push(o), !a) return;
	}
	let r = e >>> 28 & 15 | (t & 7) << 4, i = !!(t >> 3);
	if (n.push((i ? r | 128 : r) & 255), i) {
		for (let e = 3; e < 31; e += 7) {
			let r = t >>> e, i = !!(r >>> 7), a = (i ? r | 128 : r) & 255;
			if (n.push(a), !i) return;
		}
		n.push(t >>> 31 & 1);
	}
}
var ce = 4294967296;
function le(e) {
	let t = e[0] === "-";
	t && (e = e.slice(1));
	let n = 1e6, r = 0, i = 0;
	function a(t, a) {
		let o = Number(e.slice(t, a));
		i *= n, r = r * n + o, r >= ce && (i += r / ce | 0, r %= ce);
	}
	return a(-24, -18), a(-18, -12), a(-12, -6), a(-6), t ? me(r, i) : pe(r, i);
}
function ue(e, t) {
	let n = pe(e, t), r = n.hi & 2147483648;
	r && (n = me(n.lo, n.hi));
	let i = de(n.lo, n.hi);
	return r ? "-" + i : i;
}
function de(e, t) {
	if ({lo: e, hi: t} = fe(e, t), t <= 2097151) return String(ce * t + e);
	let n = e & 16777215, r = (e >>> 24 | t << 8) & 16777215, i = t >> 16 & 65535, a = n + r * 6777216 + i * 6710656, o = r + i * 8147497, s = i * 2, c = 1e7;
	return a >= c && (o += Math.floor(a / c), a %= c), o >= c && (s += Math.floor(o / c), o %= c), s.toString() + he(o) + he(a);
}
function fe(e, t) {
	return {
		lo: e >>> 0,
		hi: t >>> 0
	};
}
function pe(e, t) {
	return {
		lo: e | 0,
		hi: t | 0
	};
}
function me(e, t) {
	return t = ~t, e ? e = ~e + 1 : t += 1, pe(e, t);
}
var he = (e) => {
	let t = String(e);
	return "0000000".slice(t.length) + t;
};
function ge(e, t) {
	if (e >= 0) {
		for (; e > 127;) t.push(e & 127 | 128), e >>>= 7;
		t.push(e);
	} else {
		for (let n = 0; n < 9; n++) t.push(e & 127 | 128), e >>= 7;
		t.push(1);
	}
}
function _e() {
	let e = this.buf[this.pos++], t = e & 127;
	if (!(e & 128) || (e = this.buf[this.pos++], t |= (e & 127) << 7, !(e & 128)) || (e = this.buf[this.pos++], t |= (e & 127) << 14, !(e & 128)) || (e = this.buf[this.pos++], t |= (e & 127) << 21, !(e & 128))) return this.assertBounds(), t;
	e = this.buf[this.pos++], t |= (e & 15) << 28;
	for (let t = 5; e & 128 && t < 10; t++) e = this.buf[this.pos++];
	if (e & 128) throw Error("invalid varint");
	return this.assertBounds(), t >>> 0;
}
var h = /* @__PURE__ */ ve();
function ve() {
	let e = /* @__PURE__ */ new DataView(/* @__PURE__ */ new ArrayBuffer(8));
	if (typeof BigInt == "function" && typeof e.getBigInt64 == "function" && typeof e.getBigUint64 == "function" && typeof e.setBigInt64 == "function" && typeof e.setBigUint64 == "function" && (typeof process != "object" || {}.BUF_BIGINT_DISABLE !== "1")) {
		let t = BigInt("-9223372036854775808"), n = BigInt("9223372036854775807"), r = BigInt("0"), i = BigInt("18446744073709551615");
		return {
			zero: BigInt(0),
			supported: !0,
			parse(e) {
				let r = typeof e == "bigint" ? e : BigInt(e);
				if (r > n || r < t) throw Error(`invalid int64: ${e}`);
				return r;
			},
			uParse(e) {
				let t = typeof e == "bigint" ? e : BigInt(e);
				if (t > i || t < r) throw Error(`invalid uint64: ${e}`);
				return t;
			},
			enc(t) {
				return e.setBigInt64(0, this.parse(t), !0), {
					lo: e.getInt32(0, !0),
					hi: e.getInt32(4, !0)
				};
			},
			uEnc(t) {
				return e.setBigInt64(0, this.uParse(t), !0), {
					lo: e.getInt32(0, !0),
					hi: e.getInt32(4, !0)
				};
			},
			dec(t, n) {
				return e.setInt32(0, t, !0), e.setInt32(4, n, !0), e.getBigInt64(0, !0);
			},
			uDec(t, n) {
				return e.setInt32(0, t, !0), e.setInt32(4, n, !0), e.getBigUint64(0, !0);
			}
		};
	}
	return {
		zero: "0",
		supported: !1,
		parse(e) {
			return typeof e != "string" && (e = e.toString()), ye(e), e;
		},
		uParse(e) {
			return typeof e != "string" && (e = e.toString()), be(e), e;
		},
		enc(e) {
			return typeof e != "string" && (e = e.toString()), ye(e), le(e);
		},
		uEnc(e) {
			return typeof e != "string" && (e = e.toString()), be(e), le(e);
		},
		dec(e, t) {
			return ue(e, t);
		},
		uDec(e, t) {
			return de(e, t);
		}
	};
}
function ye(e) {
	if (!/^-?[0-9]+$/.test(e)) throw Error("invalid int64: " + e);
}
function be(e) {
	if (!/^[0-9]+$/.test(e)) throw Error("invalid uint64: " + e);
}
var g;
(function(e) {
	e[e.DOUBLE = 1] = "DOUBLE", e[e.FLOAT = 2] = "FLOAT", e[e.INT64 = 3] = "INT64", e[e.UINT64 = 4] = "UINT64", e[e.INT32 = 5] = "INT32", e[e.FIXED64 = 6] = "FIXED64", e[e.FIXED32 = 7] = "FIXED32", e[e.BOOL = 8] = "BOOL", e[e.STRING = 9] = "STRING", e[e.BYTES = 12] = "BYTES", e[e.UINT32 = 13] = "UINT32", e[e.SFIXED32 = 15] = "SFIXED32", e[e.SFIXED64 = 16] = "SFIXED64", e[e.SINT32 = 17] = "SINT32", e[e.SINT64 = 18] = "SINT64";
})(g ||= {});
function xe(e, t) {
	switch (e) {
		case g.STRING: return "";
		case g.BOOL: return !1;
		case g.DOUBLE:
		case g.FLOAT: return 0;
		case g.INT64:
		case g.UINT64:
		case g.SFIXED64:
		case g.FIXED64:
		case g.SINT64: return t ? "0" : h.zero;
		case g.BYTES: return /* @__PURE__ */ new Uint8Array();
		default: return 0;
	}
}
function Se(e, t) {
	switch (e) {
		case g.BOOL: return t === !1;
		case g.STRING: return t === "";
		case g.BYTES: return t instanceof Uint8Array && !t.byteLength;
		default: return t == 0;
	}
}
var Ce = 2, _ = Symbol.for("reflect unsafe local");
function we(e, t) {
	let n = e[t.localName].case;
	return n === void 0 ? n : t.fields.find((e) => e.localName === n);
}
function Te(e, t) {
	let n = t.localName;
	if (t.oneof) return e[t.oneof.localName].case === n;
	if (t.presence != Ce) return e[n] !== void 0 && Object.prototype.hasOwnProperty.call(e, n);
	switch (t.fieldKind) {
		case "list": return e[n].length > 0;
		case "map": return Object.keys(e[n]).length > 0;
		case "scalar": return !Se(t.scalar, e[n]);
		case "enum": return e[n] !== t.enum.values[0].number;
	}
	throw Error("message field with implicit presence");
}
function Ee(e, t) {
	return Object.prototype.hasOwnProperty.call(e, t) && e[t] !== void 0;
}
function De(e, t) {
	if (t.oneof) {
		let n = e[t.oneof.localName];
		return n.case === t.localName ? n.value : void 0;
	}
	return e[t.localName];
}
function Oe(e, t, n) {
	t.oneof ? e[t.oneof.localName] = {
		case: t.localName,
		value: n
	} : e[t.localName] = n;
}
function ke(e, t) {
	let n = t.localName;
	if (t.oneof) {
		let r = t.oneof.localName;
		e[r].case === n && (e[r] = { case: void 0 });
	} else if (t.presence != Ce) delete e[n];
	else switch (t.fieldKind) {
		case "map":
			e[n] = {};
			break;
		case "list":
			e[n] = [];
			break;
		case "enum":
			e[n] = t.enum.values[0].number;
			break;
		case "scalar": e[n] = xe(t.scalar, t.longAsString);
	}
}
function Ae(e) {
	for (let t of e.field) Ee(t, "jsonName") || (t.jsonName = re(t.name));
	e.nestedType.forEach(Ae);
}
function je(e, t) {
	let n = e.values.find((e) => e.name === t);
	if (!n) throw Error(`cannot parse ${e} default value: ${t}`);
	return n.number;
}
function Me(e, t) {
	switch (e) {
		case g.STRING: return t;
		case g.BYTES: {
			let n = Ne(t);
			if (n === !1) throw Error(`cannot parse ${g[e]} default value: ${t}`);
			return n;
		}
		case g.INT64:
		case g.SFIXED64:
		case g.SINT64: return h.parse(t);
		case g.UINT64:
		case g.FIXED64: return h.uParse(t);
		case g.DOUBLE:
		case g.FLOAT: switch (t) {
			case "inf": return Infinity;
			case "-inf": return -Infinity;
			case "nan": return NaN;
			default: return parseFloat(t);
		}
		case g.BOOL: return t === "true";
		case g.INT32:
		case g.UINT32:
		case g.SINT32:
		case g.FIXED32:
		case g.SFIXED32: return parseInt(t, 10);
	}
}
function Ne(e) {
	let t = [], n = {
		tail: e,
		c: "",
		next() {
			return this.tail.length != 0 && (this.c = this.tail[0], this.tail = this.tail.substring(1), !0);
		},
		take(e) {
			if (this.tail.length >= e) {
				let t = this.tail.substring(0, e);
				return this.tail = this.tail.substring(e), t;
			}
			return !1;
		}
	};
	for (; n.next();) switch (n.c) {
		case "\\":
			if (n.next()) switch (n.c) {
				case "\\":
					t.push(n.c.charCodeAt(0));
					break;
				case "b":
					t.push(8);
					break;
				case "f":
					t.push(12);
					break;
				case "n":
					t.push(10);
					break;
				case "r":
					t.push(13);
					break;
				case "t":
					t.push(9);
					break;
				case "v":
					t.push(11);
					break;
				case "0":
				case "1":
				case "2":
				case "3":
				case "4":
				case "5":
				case "6":
				case "7": {
					let e = n.c, r = n.take(2);
					if (r === !1) return !1;
					let i = parseInt(e + r, 8);
					if (Number.isNaN(i)) return !1;
					t.push(i);
					break;
				}
				case "x": {
					let e = n.c, r = n.take(2);
					if (r === !1) return !1;
					let i = parseInt(e + r, 16);
					if (Number.isNaN(i)) return !1;
					t.push(i);
					break;
				}
				case "u": {
					let e = n.c, r = n.take(4);
					if (r === !1) return !1;
					let i = parseInt(e + r, 16);
					if (Number.isNaN(i)) return !1;
					let a = /* @__PURE__ */ new Uint8Array(4);
					new DataView(a.buffer).setInt32(0, i, !0), t.push(a[0], a[1], a[2], a[3]);
					break;
				}
				case "U": {
					let e = n.c, r = n.take(8);
					if (r === !1) return !1;
					let i = h.uEnc(e + r), a = /* @__PURE__ */ new Uint8Array(8), o = new DataView(a.buffer);
					o.setInt32(0, i.lo, !0), o.setInt32(4, i.hi, !0), t.push(a[0], a[1], a[2], a[3], a[4], a[5], a[6], a[7]);
					break;
				}
			}
			break;
		default: t.push(n.c.charCodeAt(0));
	}
	return new Uint8Array(t);
}
function* Pe(e) {
	switch (e.kind) {
		case "file":
			for (let t of e.messages) yield t, yield* Pe(t);
			yield* e.enums, yield* e.services, yield* e.extensions;
			break;
		case "message":
			for (let t of e.nestedMessages) yield t, yield* Pe(t);
			yield* e.nestedEnums, yield* e.nestedExtensions;
	}
}
function Fe(...e) {
	let t = Ie();
	if (!e.length) return t;
	if ("$typeName" in e[0] && e[0].$typeName == "google.protobuf.FileDescriptorSet") {
		for (let n of e[0].file) tt(n, t);
		return t;
	}
	if ("$typeName" in e[0]) {
		let n = e[0], r = e[1], i = /* @__PURE__ */ new Set();
		function a(e) {
			let n = [];
			for (let a of e.dependency) {
				if (t.getFile(a) != null || i.has(a)) continue;
				let o = r(a);
				if (!o) throw Error(`Unable to resolve ${a}, imported by ${e.name}`);
				"kind" in o ? t.addFile(o, !1, !0) : (i.add(o.name), n.push(o));
			}
			return n.concat(...n.map(a));
		}
		for (let e of [n, ...a(n)].reverse()) tt(e, t);
	} else for (let n of e) for (let e of n.files) t.addFile(e);
	return t;
}
function Ie() {
	let e = /* @__PURE__ */ new Map(), t = /* @__PURE__ */ new Map(), n = /* @__PURE__ */ new Map();
	return {
		kind: "registry",
		types: e,
		extendees: t,
		[Symbol.iterator]() {
			return e.values();
		},
		get files() {
			return n.values();
		},
		addFile(e, t, r) {
			if (n.set(e.proto.name, e), !t) for (let t of Pe(e)) this.add(t);
			if (r) for (let n of e.dependencies) this.addFile(n, t, r);
		},
		add(n) {
			if (n.kind == "extension") {
				let e = t.get(n.extendee.typeName);
				e || t.set(n.extendee.typeName, e = /* @__PURE__ */ new Map()), e.set(n.number, n);
			}
			e.set(n.typeName, n);
		},
		get(t) {
			return e.get(t);
		},
		getFile(e) {
			return n.get(e);
		},
		getMessage(t) {
			let n = e.get(t);
			return n?.kind == "message" ? n : void 0;
		},
		getEnum(t) {
			let n = e.get(t);
			return n?.kind == "enum" ? n : void 0;
		},
		getExtension(t) {
			let n = e.get(t);
			return n?.kind == "extension" ? n : void 0;
		},
		getExtensionFor(e, n) {
			return t.get(e.typeName)?.get(n);
		},
		getService(t) {
			let n = e.get(t);
			return n?.kind == "service" ? n : void 0;
		}
	};
}
var Le = 998, Re = 999, ze = 9, Be = 10, Ve = 11, He = 12, Ue = 14, We = 3, Ge = 2, Ke = 1, qe = 0, Je = 1, Ye = 2, Xe = 3, Ze = 1, Qe = 2, $e = 1, et = {
	998: {
		fieldPresence: 1,
		enumType: 2,
		repeatedFieldEncoding: 2,
		utf8Validation: 3,
		messageEncoding: 1,
		jsonFormat: 2,
		enforceNamingStyle: 2,
		defaultSymbolVisibility: 1
	},
	999: {
		fieldPresence: 2,
		enumType: 1,
		repeatedFieldEncoding: 1,
		utf8Validation: 2,
		messageEncoding: 1,
		jsonFormat: 1,
		enforceNamingStyle: 2,
		defaultSymbolVisibility: 1
	},
	1e3: {
		fieldPresence: 1,
		enumType: 1,
		repeatedFieldEncoding: 1,
		utf8Validation: 2,
		messageEncoding: 1,
		jsonFormat: 1,
		enforceNamingStyle: 2,
		defaultSymbolVisibility: 1
	},
	1001: {
		fieldPresence: 1,
		enumType: 1,
		repeatedFieldEncoding: 1,
		utf8Validation: 2,
		messageEncoding: 1,
		jsonFormat: 1,
		enforceNamingStyle: 1,
		defaultSymbolVisibility: 2
	}
};
function tt(e, t) {
	let n = {
		kind: "file",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		edition: ut(e),
		name: e.name.replace(/\.proto$/, ""),
		dependencies: dt(e, t),
		enums: [],
		messages: [],
		extensions: [],
		services: [],
		toString() {
			return `file ${e.name}`;
		}
	}, r = /* @__PURE__ */ new Map(), i = {
		get(e) {
			return r.get(e);
		},
		add(e) {
			y(e.proto.options?.mapEntry === !0), r.set(e.typeName, e);
		}
	};
	for (let r of e.enumType) it(r, n, void 0, t);
	for (let r of e.messageType) at(r, n, void 0, t, i);
	for (let r of e.service) ot(r, n, t);
	nt(n, t);
	for (let e of r.values()) rt(e, t, i);
	for (let e of n.messages) rt(e, t, i), nt(e, t);
	t.addFile(n, !0);
}
function nt(e, t) {
	switch (e.kind) {
		case "file":
			for (let n of e.proto.extension) {
				let r = lt(n, e, t);
				e.extensions.push(r), t.add(r);
			}
			break;
		case "message":
			for (let n of e.proto.extension) {
				let r = lt(n, e, t);
				e.nestedExtensions.push(r), t.add(r);
			}
			for (let n of e.nestedMessages) nt(n, t);
	}
}
function rt(e, t, n) {
	let r = e.proto.oneofDecl.map((t) => ct(t, e)), i = /* @__PURE__ */ new Set();
	for (let a of e.proto.field) {
		let o = ht(a, r), s = lt(a, e, t, o, n);
		e.fields.push(s), e.field[s.localName] = s, o === void 0 ? e.members.push(s) : (o.fields.push(s), i.has(o) || (i.add(o), e.members.push(o)));
	}
	for (let t of r.filter((e) => i.has(e))) e.oneofs.push(t);
	for (let r of e.nestedMessages) rt(r, t, n);
}
function it(e, t, n, r) {
	let i = ft(e.name, e.value), a = {
		kind: "enum",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		file: t,
		parent: n,
		open: !0,
		name: e.name,
		typeName: mt(e, n, t),
		value: {},
		values: [],
		sharedPrefix: i,
		toString() {
			return `enum ${this.typeName}`;
		}
	};
	a.open = yt(a), r.add(a);
	for (let t of e.value) {
		let e = t.name;
		a.values.push(a.value[t.number] = {
			kind: "enum_value",
			proto: t,
			deprecated: t.options?.deprecated ?? !1,
			parent: a,
			name: e,
			localName: ae(i == null ? e : e.substring(i.length)),
			number: t.number,
			toString() {
				return `enum value ${a.typeName}.${e}`;
			}
		});
	}
	(n?.nestedEnums ?? t.enums).push(a);
}
function at(e, t, n, r, i) {
	let a = {
		kind: "message",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		file: t,
		parent: n,
		name: e.name,
		typeName: mt(e, n, t),
		fields: [],
		field: {},
		oneofs: [],
		members: [],
		nestedEnums: [],
		nestedMessages: [],
		nestedExtensions: [],
		toString() {
			return `message ${this.typeName}`;
		}
	};
	e.options?.mapEntry === !0 ? i.add(a) : ((n?.nestedMessages ?? t.messages).push(a), r.add(a));
	for (let n of e.enumType) it(n, t, a, r);
	for (let n of e.nestedType) at(n, t, a, r, i);
}
function ot(e, t, n) {
	let r = {
		kind: "service",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		file: t,
		name: e.name,
		typeName: mt(e, void 0, t),
		methods: [],
		method: {},
		toString() {
			return `service ${this.typeName}`;
		}
	};
	t.services.push(r), n.add(r);
	for (let t of e.method) {
		let e = st(t, r, n);
		r.methods.push(e), r.method[e.localName] = e;
	}
}
function st(e, t, n) {
	let r;
	r = e.clientStreaming && e.serverStreaming ? "bidi_streaming" : e.clientStreaming ? "client_streaming" : e.serverStreaming ? "server_streaming" : "unary";
	let i = n.getMessage(v(e.inputType)), a = n.getMessage(v(e.outputType));
	y(i, `invalid MethodDescriptorProto: input_type ${e.inputType} not found`), y(a, `invalid MethodDescriptorProto: output_type ${e.inputType} not found`);
	let o = e.name;
	return {
		kind: "rpc",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		parent: t,
		name: o,
		localName: ae(o.length ? ae(o[0].toLowerCase() + o.substring(1)) : o),
		methodKind: r,
		input: i,
		output: a,
		idempotency: e.options?.idempotencyLevel ?? qe,
		toString() {
			return `rpc ${t.typeName}.${o}`;
		}
	};
}
function ct(e, t) {
	return {
		kind: "oneof",
		proto: e,
		deprecated: !1,
		parent: t,
		fields: [],
		name: e.name,
		localName: ae(re(e.name)),
		toString() {
			return `oneof ${t.typeName}.${this.name}`;
		}
	};
}
function lt(e, t, n, r, i) {
	let a = i === void 0, o = {
		kind: "field",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		name: e.name,
		number: e.number,
		scalar: void 0,
		message: void 0,
		enum: void 0,
		presence: gt(e, r, a, t),
		listKind: void 0,
		mapKind: void 0,
		mapKey: void 0,
		delimitedEncoding: void 0,
		packed: void 0,
		longAsString: !1,
		getDefaultValue: void 0
	};
	if (a) {
		let r = t.kind == "file" ? t : t.file, i = t.kind == "file" ? void 0 : t, a = mt(e, i, r);
		o.kind = "extension", o.file = r, o.parent = i, o.oneof = void 0, o.typeName = a, o.jsonName = `[${a}]`, o.toString = () => `extension ${a}`;
		let s = n.getMessage(v(e.extendee));
		y(s, `invalid FieldDescriptorProto: extendee ${e.extendee} not found`), o.extendee = s;
	} else {
		let n = t;
		y(n.kind == "message"), o.parent = n, o.oneof = r, o.localName = r ? re(e.name) : ae(re(e.name)), o.jsonName = e.jsonName, o.toString = () => `field ${n.typeName}.${e.name}`;
	}
	let s = e.label, c = e.type, l = e.options?.jstype;
	if (s === We) {
		let r = c == Ve ? i?.get(v(e.typeName)) : void 0;
		if (r) {
			o.fieldKind = "map";
			let { key: e, value: t } = vt(r);
			return o.mapKey = e.scalar, o.mapKind = t.fieldKind, o.message = t.message, o.delimitedEncoding = !1, o.enum = t.enum, o.scalar = t.scalar, o;
		}
		switch (o.fieldKind = "list", c) {
			case Ve:
			case Be:
				o.listKind = "message", o.message = n.getMessage(v(e.typeName)), y(o.message), o.delimitedEncoding = bt(e, t);
				break;
			case Ue:
				o.listKind = "enum", o.enum = n.getEnum(v(e.typeName)), y(o.enum);
				break;
			default: o.listKind = "scalar", o.scalar = c, o.longAsString = l == Ke;
		}
		return o.packed = _t(e, t), o;
	}
	switch (c) {
		case Ve:
		case Be:
			o.fieldKind = "message", o.message = n.getMessage(v(e.typeName)), y(o.message, `invalid FieldDescriptorProto: type_name ${e.typeName} not found`), o.delimitedEncoding = bt(e, t), o.getDefaultValue = () => void 0;
			break;
		case Ue: {
			let t = n.getEnum(v(e.typeName));
			y(t !== void 0, `invalid FieldDescriptorProto: type_name ${e.typeName} not found`), o.fieldKind = "enum", o.enum = n.getEnum(v(e.typeName)), o.getDefaultValue = () => Ee(e, "defaultValue") ? je(t, e.defaultValue) : void 0;
			break;
		}
		default: o.fieldKind = "scalar", o.scalar = c, o.longAsString = l == Ke, o.getDefaultValue = () => Ee(e, "defaultValue") ? Me(c, e.defaultValue) : void 0;
	}
	return o;
}
function ut(e) {
	switch (e.syntax) {
		case "":
		case "proto2": return Le;
		case "proto3": return Re;
		case "editions":
			if (e.edition in et) return e.edition;
			throw Error(`${e.name}: unsupported edition`);
		default: throw Error(`${e.name}: unsupported syntax "${e.syntax}"`);
	}
}
function dt(e, t) {
	return e.dependency.map((n) => {
		let r = t.getFile(n);
		if (!r) throw Error(`Cannot find ${n}, imported by ${e.name}`);
		return r;
	});
}
function ft(e, t) {
	let n = pt(e) + "_";
	for (let e of t) {
		if (!e.name.toLowerCase().startsWith(n)) return;
		let t = e.name.substring(n.length);
		if (t.length == 0 || /^\d/.test(t)) return;
	}
	return n;
}
function pt(e) {
	return (e.substring(0, 1) + e.substring(1).replace(/[A-Z]/g, (e) => "_" + e)).toLowerCase();
}
function mt(e, t, n) {
	let r;
	return r = t ? `${t.typeName}.${e.name}` : n.proto.package.length > 0 ? `${n.proto.package}.${e.name}` : `${e.name}`, r;
}
function v(e) {
	return e.startsWith(".") ? e.substring(1) : e;
}
function ht(e, t) {
	if (!Ee(e, "oneofIndex") || e.proto3Optional) return;
	let n = t[e.oneofIndex];
	return y(n, `invalid FieldDescriptorProto: oneof #${e.oneofIndex} for field #${e.number} not found`), n;
}
function gt(e, t, n, r) {
	if (e.label == Ge) return Xe;
	if (e.label == We) return Ye;
	if (t || e.proto3Optional || n) return Je;
	let i = xt("fieldPresence", {
		proto: e,
		parent: r
	});
	return i == Ye && (e.type == Ve || e.type == Be) ? Je : i;
}
function _t(e, t) {
	if (e.label != We) return !1;
	switch (e.type) {
		case ze:
		case He:
		case Be:
		case Ve: return !1;
	}
	let n = e.options;
	return n && Ee(n, "packed") ? n.packed : Ze == xt("repeatedFieldEncoding", {
		proto: e,
		parent: t
	});
}
function vt(e) {
	let t = e.fields.find((e) => e.number === 1), n = e.fields.find((e) => e.number === 2);
	return y(t && t.fieldKind == "scalar" && t.scalar != g.BYTES && t.scalar != g.FLOAT && t.scalar != g.DOUBLE && n && n.fieldKind != "list" && n.fieldKind != "map"), {
		key: t,
		value: n
	};
}
function yt(e) {
	return $e == xt("enumType", {
		proto: e.proto,
		parent: e.parent ?? e.file
	});
}
function bt(e, t) {
	return e.type == Be || Qe == xt("messageEncoding", {
		proto: e,
		parent: t
	});
}
function xt(e, t) {
	let n = t.proto.options?.features;
	if (n) {
		let t = n[e];
		if (t != 0) return t;
	}
	if ("kind" in t) {
		if (t.kind == "message") return xt(e, t.parent ?? t.file);
		let n = et[t.edition];
		if (!n) throw Error(`feature default for edition ${t.edition} not found`);
		return n[e];
	}
	return xt(e, t.parent);
}
function y(e, t) {
	if (!e) throw Error(t);
}
function St(e) {
	let t = Ct(e);
	return t.messageType.forEach(Ae), Fe(t, () => void 0).getFile(t.name);
}
function Ct(e) {
	return Object.assign(Object.create({
		syntax: "",
		edition: 0
	}), Object.assign(Object.assign({
		$typeName: "google.protobuf.FileDescriptorProto",
		dependency: [],
		publicDependency: [],
		weakDependency: [],
		optionDependency: [],
		service: [],
		extension: []
	}, e), {
		messageType: e.messageType.map(wt),
		enumType: e.enumType.map(Dt)
	}));
}
function wt(e) {
	return Object.assign(Object.create({ visibility: 0 }), {
		$typeName: "google.protobuf.DescriptorProto",
		name: e.name,
		field: e.field?.map(Tt) ?? [],
		extension: [],
		nestedType: e.nestedType?.map(wt) ?? [],
		enumType: e.enumType?.map(Dt) ?? [],
		extensionRange: e.extensionRange?.map((e) => Object.assign({ $typeName: "google.protobuf.DescriptorProto.ExtensionRange" }, e)) ?? [],
		oneofDecl: [],
		reservedRange: [],
		reservedName: []
	});
}
function Tt(e) {
	return Object.assign(Object.create({
		label: 1,
		typeName: "",
		extendee: "",
		defaultValue: "",
		oneofIndex: 0,
		jsonName: "",
		proto3Optional: !1
	}), Object.assign(Object.assign({ $typeName: "google.protobuf.FieldDescriptorProto" }, e), { options: e.options ? Et(e.options) : void 0 }));
}
function Et(e) {
	return Object.assign(Object.create({
		ctype: 0,
		packed: !1,
		jstype: 0,
		lazy: !1,
		unverifiedLazy: !1,
		deprecated: !1,
		weak: !1,
		debugRedact: !1,
		retention: 0
	}), Object.assign(Object.assign({ $typeName: "google.protobuf.FieldOptions" }, e), {
		targets: e.targets ?? [],
		editionDefaults: e.editionDefaults?.map((e) => Object.assign({ $typeName: "google.protobuf.FieldOptions.EditionDefault" }, e)) ?? [],
		uninterpretedOption: []
	}));
}
function Dt(e) {
	return Object.assign(Object.create({ visibility: 0 }), {
		$typeName: "google.protobuf.EnumDescriptorProto",
		name: e.name,
		reservedName: [],
		reservedRange: [],
		value: e.value.map((e) => Object.assign({ $typeName: "google.protobuf.EnumValueDescriptorProto" }, e))
	});
}
function Ot(e) {
	let t = Nt(), n = e.length * 3 / 4;
	e[e.length - 2] == "=" ? n -= 2 : e[e.length - 1] == "=" && --n;
	let r = new Uint8Array(n), i = 0, a = 0, o, s = 0;
	for (let n = 0; n < e.length; n++) {
		if (o = t[e.charCodeAt(n)], o === void 0) switch (e[n]) {
			case "=": a = 0;
			case "\n":
			case "\r":
			case "	":
			case " ": continue;
			default: throw Error("invalid base64 string");
		}
		switch (a) {
			case 0:
				s = o, a = 1;
				break;
			case 1:
				r[i++] = s << 2 | (o & 48) >> 4, s = o, a = 2;
				break;
			case 2:
				r[i++] = (s & 15) << 4 | (o & 60) >> 2, s = o, a = 3;
				break;
			case 3: r[i++] = (s & 3) << 6 | o, a = 0;
		}
	}
	if (a == 1) throw Error("invalid base64 string");
	return r.subarray(0, i);
}
var kt, At, jt;
function Mt(e) {
	return kt || (kt = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split(""), At = kt.slice(0, -2).concat("-", "_")), e == "url" ? At : kt;
}
function Nt() {
	if (!jt) {
		jt = [];
		let e = Mt("std");
		for (let t = 0; t < e.length; t++) jt[e[t].charCodeAt(0)] = t;
		jt[45] = e.indexOf("+"), jt[95] = e.indexOf("/");
	}
	return jt;
}
function Pt(e, t) {
	return typeof e == "object" && e && "$typeName" in e && typeof e.$typeName == "string" ? t === void 0 || t.typeName === e.$typeName : !1;
}
var Ft = class extends Error {
	constructor(e, t, n = "FieldValueInvalidError") {
		super(t), this.name = n, this.field = () => e;
	}
};
function b(e) {
	return typeof e == "object" && !!e && !Array.isArray(e);
}
function It(e, t) {
	if (b(e) && _ in e && "add" in e && "field" in e && typeof e.field == "function") {
		if (t !== void 0) {
			let n = t, r = e.field();
			return n.listKind == r.listKind && n.scalar === r.scalar && n.message?.typeName === r.message?.typeName && n.enum?.typeName === r.enum?.typeName;
		}
		return !0;
	}
	return !1;
}
function Lt(e, t) {
	if (b(e) && _ in e && "has" in e && "field" in e && typeof e.field == "function") {
		if (t !== void 0) {
			let n = t, r = e.field();
			return n.mapKey === r.mapKey && n.mapKind == r.mapKind && n.scalar === r.scalar && n.message?.typeName === r.message?.typeName && n.enum?.typeName === r.enum?.typeName;
		}
		return !0;
	}
	return !1;
}
function Rt(e, t) {
	return b(e) && _ in e && "desc" in e && b(e.desc) && e.desc.kind === "message" && (t === void 0 || e.desc.typeName == t.typeName);
}
var zt = Symbol.for("@bufbuild/protobuf/text-encoding");
function Bt() {
	if (globalThis[zt] == null) {
		let e = new globalThis.TextEncoder(), t = new globalThis.TextDecoder();
		globalThis[zt] = {
			encodeUtf8(t) {
				return e.encode(t);
			},
			decodeUtf8(e) {
				return t.decode(e);
			},
			checkUtf8(e) {
				try {
					return !0;
				} catch {
					return !1;
				}
			}
		};
	}
	return globalThis[zt];
}
var x;
(function(e) {
	e[e.Varint = 0] = "Varint", e[e.Bit64 = 1] = "Bit64", e[e.LengthDelimited = 2] = "LengthDelimited", e[e.StartGroup = 3] = "StartGroup", e[e.EndGroup = 4] = "EndGroup", e[e.Bit32 = 5] = "Bit32";
})(x ||= {});
var Vt = 34028234663852886e22, Ht = -34028234663852886e22, Ut = 4294967295, Wt = 2147483647, Gt = -2147483648, Kt = class {
	constructor(e = Bt().encodeUtf8) {
		this.encodeUtf8 = e, this.stack = [], this.chunks = [], this.buf = [];
	}
	finish() {
		this.buf.length && (this.chunks.push(new Uint8Array(this.buf)), this.buf = []);
		let e = 0;
		for (let t = 0; t < this.chunks.length; t++) e += this.chunks[t].length;
		let t = new Uint8Array(e), n = 0;
		for (let e = 0; e < this.chunks.length; e++) t.set(this.chunks[e], n), n += this.chunks[e].length;
		return this.chunks = [], t;
	}
	fork() {
		return this.stack.push({
			chunks: this.chunks,
			buf: this.buf
		}), this.chunks = [], this.buf = [], this;
	}
	join() {
		let e = this.finish(), t = this.stack.pop();
		if (!t) throw Error("invalid state, fork stack empty");
		return this.chunks = t.chunks, this.buf = t.buf, this.uint32(e.byteLength), this.raw(e);
	}
	tag(e, t) {
		return this.uint32((e << 3 | t) >>> 0);
	}
	raw(e) {
		return this.buf.length && (this.chunks.push(new Uint8Array(this.buf)), this.buf = []), this.chunks.push(e), this;
	}
	uint32(e) {
		for (Yt(e); e > 127;) this.buf.push(e & 127 | 128), e >>>= 7;
		return this.buf.push(e), this;
	}
	int32(e) {
		return Jt(e), ge(e, this.buf), this;
	}
	bool(e) {
		return this.buf.push(+!!e), this;
	}
	bytes(e) {
		return this.uint32(e.byteLength), this.raw(e);
	}
	string(e) {
		let t = this.encodeUtf8(e);
		return this.uint32(t.byteLength), this.raw(t);
	}
	float(e) {
		Xt(e);
		let t = /* @__PURE__ */ new Uint8Array(4);
		return new DataView(t.buffer).setFloat32(0, e, !0), this.raw(t);
	}
	double(e) {
		let t = /* @__PURE__ */ new Uint8Array(8);
		return new DataView(t.buffer).setFloat64(0, e, !0), this.raw(t);
	}
	fixed32(e) {
		Yt(e);
		let t = /* @__PURE__ */ new Uint8Array(4);
		return new DataView(t.buffer).setUint32(0, e, !0), this.raw(t);
	}
	sfixed32(e) {
		Jt(e);
		let t = /* @__PURE__ */ new Uint8Array(4);
		return new DataView(t.buffer).setInt32(0, e, !0), this.raw(t);
	}
	sint32(e) {
		return Jt(e), e = (e << 1 ^ e >> 31) >>> 0, ge(e, this.buf), this;
	}
	sfixed64(e) {
		let t = /* @__PURE__ */ new Uint8Array(8), n = new DataView(t.buffer), r = h.enc(e);
		return n.setInt32(0, r.lo, !0), n.setInt32(4, r.hi, !0), this.raw(t);
	}
	fixed64(e) {
		let t = /* @__PURE__ */ new Uint8Array(8), n = new DataView(t.buffer), r = h.uEnc(e);
		return n.setInt32(0, r.lo, !0), n.setInt32(4, r.hi, !0), this.raw(t);
	}
	int64(e) {
		let t = h.enc(e);
		return se(t.lo, t.hi, this.buf), this;
	}
	sint64(e) {
		let t = h.enc(e), n = t.hi >> 31;
		return se(t.lo << 1 ^ n, (t.hi << 1 | t.lo >>> 31) ^ n, this.buf), this;
	}
	uint64(e) {
		let t = h.uEnc(e);
		return se(t.lo, t.hi, this.buf), this;
	}
}, qt = class {
	constructor(e, t = Bt().decodeUtf8) {
		this.decodeUtf8 = t, this.varint64 = oe, this.uint32 = _e, this.buf = e, this.len = e.length, this.pos = 0, this.view = new DataView(e.buffer, e.byteOffset, e.byteLength);
	}
	tag() {
		let e = this.uint32(), t = e >>> 3, n = e & 7;
		if (t <= 0 || n < 0 || n > 5) throw Error("illegal tag: field no " + t + " wire type " + n);
		return [t, n];
	}
	skip(e, t) {
		let n = this.pos;
		switch (e) {
			case x.Varint:
				for (; this.buf[this.pos++] & 128;);
				break;
			case x.Bit64: this.pos += 4;
			case x.Bit32:
				this.pos += 4;
				break;
			case x.LengthDelimited:
				let n = this.uint32();
				this.pos += n;
				break;
			case x.StartGroup:
				for (;;) {
					let [e, n] = this.tag();
					if (n === x.EndGroup) {
						if (t !== void 0 && e !== t) throw Error("invalid end group tag");
						break;
					}
					this.skip(n, e);
				}
				break;
			default: throw Error("cant skip wire type " + e);
		}
		return this.assertBounds(), this.buf.subarray(n, this.pos);
	}
	assertBounds() {
		if (this.pos > this.len) throw RangeError("premature EOF");
	}
	int32() {
		return this.uint32() | 0;
	}
	sint32() {
		let e = this.uint32();
		return e >>> 1 ^ -(e & 1);
	}
	int64() {
		return h.dec(...this.varint64());
	}
	uint64() {
		return h.uDec(...this.varint64());
	}
	sint64() {
		let [e, t] = this.varint64(), n = -(e & 1);
		return e = (e >>> 1 | (t & 1) << 31) ^ n, t = t >>> 1 ^ n, h.dec(e, t);
	}
	bool() {
		let [e, t] = this.varint64();
		return e !== 0 || t !== 0;
	}
	fixed32() {
		return this.view.getUint32((this.pos += 4) - 4, !0);
	}
	sfixed32() {
		return this.view.getInt32((this.pos += 4) - 4, !0);
	}
	fixed64() {
		return h.uDec(this.sfixed32(), this.sfixed32());
	}
	sfixed64() {
		return h.dec(this.sfixed32(), this.sfixed32());
	}
	float() {
		return this.view.getFloat32((this.pos += 4) - 4, !0);
	}
	double() {
		return this.view.getFloat64((this.pos += 8) - 8, !0);
	}
	bytes() {
		let e = this.uint32(), t = this.pos;
		return this.pos += e, this.assertBounds(), this.buf.subarray(t, t + e);
	}
	string() {
		return this.decodeUtf8(this.bytes());
	}
};
function Jt(e) {
	if (typeof e == "string") e = Number(e);
	else if (typeof e != "number") throw Error("invalid int32: " + typeof e);
	if (!Number.isInteger(e) || e > Wt || e < Gt) throw Error("invalid int32: " + e);
}
function Yt(e) {
	if (typeof e == "string") e = Number(e);
	else if (typeof e != "number") throw Error("invalid uint32: " + typeof e);
	if (!Number.isInteger(e) || e > Ut || e < 0) throw Error("invalid uint32: " + e);
}
function Xt(e) {
	if (typeof e == "string") {
		let t = e;
		if (e = Number(e), Number.isNaN(e) && t !== "NaN") throw Error("invalid float32: " + t);
	} else if (typeof e != "number") throw Error("invalid float32: " + typeof e);
	if (Number.isFinite(e) && (e > Vt || e < Ht)) throw Error("invalid float32: " + e);
}
function Zt(e, t) {
	let n = e.fieldKind == "list" ? It(t, e) : e.fieldKind == "map" ? Lt(t, e) : en(e, t);
	if (n === !0) return;
	let r;
	switch (e.fieldKind) {
		case "list":
			r = `expected ${on(e)}, got ${rn(t)}`;
			break;
		case "map":
			r = `expected ${sn(e)}, got ${rn(t)}`;
			break;
		default: r = nn(e, t, n);
	}
	return new Ft(e, r);
}
function Qt(e, t, n) {
	let r = en(e, n);
	if (r !== !0) return new Ft(e, `list item #${t + 1}: ${nn(e, n, r)}`);
}
function $t(e, t, n) {
	let r = tn(t, e.mapKey);
	if (r !== !0) return new Ft(e, `invalid map key: ${nn({ scalar: e.mapKey }, t, r)}`);
	let i = en(e, n);
	if (i !== !0) return new Ft(e, `map entry ${rn(t)}: ${nn(e, n, i)}`);
}
function en(e, t) {
	return e.scalar === void 0 ? e.enum === void 0 ? Rt(t, e.message) : e.enum.open ? Number.isInteger(t) : e.enum.values.some((e) => e.number === t) : tn(t, e.scalar);
}
function tn(e, t) {
	switch (t) {
		case g.DOUBLE: return typeof e == "number";
		case g.FLOAT: return typeof e == "number" ? Number.isNaN(e) || !Number.isFinite(e) ? !0 : e > Vt || e < Ht ? `${e.toFixed()} out of range` : !0 : !1;
		case g.INT32:
		case g.SFIXED32:
		case g.SINT32: return typeof e != "number" || !Number.isInteger(e) ? !1 : e > Wt || e < Gt ? `${e.toFixed()} out of range` : !0;
		case g.FIXED32:
		case g.UINT32: return typeof e != "number" || !Number.isInteger(e) ? !1 : e > Ut || e < 0 ? `${e.toFixed()} out of range` : !0;
		case g.BOOL: return typeof e == "boolean";
		case g.STRING: return typeof e == "string" ? Bt().checkUtf8(e) || "invalid UTF8" : !1;
		case g.BYTES: return e instanceof Uint8Array;
		case g.INT64:
		case g.SFIXED64:
		case g.SINT64:
			if (typeof e == "bigint" || typeof e == "number" || typeof e == "string" && e.length > 0) try {
				return h.parse(e), !0;
			} catch {
				return `${e} out of range`;
			}
			return !1;
		case g.FIXED64:
		case g.UINT64:
			if (typeof e == "bigint" || typeof e == "number" || typeof e == "string" && e.length > 0) try {
				return h.uParse(e), !0;
			} catch {
				return `${e} out of range`;
			}
			return !1;
	}
}
function nn(e, t, n) {
	return n = typeof n == "string" ? `: ${n}` : `, got ${rn(t)}`, e.scalar === void 0 ? e.enum === void 0 ? `expected ${an(e.message)}` + n : `expected ${e.enum.toString()}` + n : `expected ${cn(e.scalar)}` + n;
}
function rn(e) {
	switch (typeof e) {
		case "object": return e === null ? "null" : e instanceof Uint8Array ? `Uint8Array(${e.length})` : Array.isArray(e) ? `Array(${e.length})` : It(e) ? on(e.field()) : Lt(e) ? sn(e.field()) : Rt(e) ? an(e.desc) : Pt(e) ? `message ${e.$typeName}` : "object";
		case "string": return e.length > 30 ? "string" : `"${e.split("\"").join("\\\"")}"`;
		case "boolean": return String(e);
		case "number": return String(e);
		case "bigint": return String(e) + "n";
		default: return typeof e;
	}
}
function an(e) {
	return `ReflectMessage (${e.typeName})`;
}
function on(e) {
	switch (e.listKind) {
		case "message": return `ReflectList (${e.message.toString()})`;
		case "enum": return `ReflectList (${e.enum.toString()})`;
		case "scalar": return `ReflectList (${g[e.scalar]})`;
	}
}
function sn(e) {
	switch (e.mapKind) {
		case "message": return `ReflectMap (${g[e.mapKey]}, ${e.message.toString()})`;
		case "enum": return `ReflectMap (${g[e.mapKey]}, ${e.enum.toString()})`;
		case "scalar": return `ReflectMap (${g[e.mapKey]}, ${g[e.scalar]})`;
	}
}
function cn(e) {
	switch (e) {
		case g.STRING: return "string";
		case g.BOOL: return "boolean";
		case g.INT64:
		case g.SINT64:
		case g.SFIXED64: return "bigint (int64)";
		case g.UINT64:
		case g.FIXED64: return "bigint (uint64)";
		case g.BYTES: return "Uint8Array";
		case g.DOUBLE: return "number (float64)";
		case g.FLOAT: return "number (float32)";
		case g.FIXED32:
		case g.UINT32: return "number (uint32)";
		case g.INT32:
		case g.SFIXED32:
		case g.SINT32: return "number (int32)";
	}
}
function ln(e) {
	return dn(e.$typeName);
}
function un(e) {
	let t = e.fields[0];
	return dn(e.typeName) && t !== void 0 && t.fieldKind == "scalar" && t.name == "value" && t.number == 1;
}
function dn(e) {
	return e.startsWith("google.protobuf.") && [
		"DoubleValue",
		"FloatValue",
		"Int64Value",
		"UInt64Value",
		"Int32Value",
		"UInt32Value",
		"BoolValue",
		"StringValue",
		"BytesValue"
	].includes(e.substring(16));
}
var fn = 999, pn = 998, mn = 2;
function S(e, t) {
	if (Pt(t, e)) return t;
	let n = wn(e);
	return t !== void 0 && hn(e, n, t), n;
}
function hn(e, t, n) {
	for (let r of e.members) {
		let e = n[r.localName];
		if (e == null) continue;
		let i;
		if (r.kind == "oneof") {
			let t = we(n, r);
			if (!t) continue;
			i = t, e = De(n, t);
		} else i = r;
		switch (i.fieldKind) {
			case "message":
				e = yn(i, e);
				break;
			case "scalar":
				e = gn(i, e);
				break;
			case "list":
				e = vn(i, e);
				break;
			case "map": e = _n(i, e);
		}
		Oe(t, i, e);
	}
	return t;
}
function gn(e, t) {
	return e.scalar == g.BYTES ? bn(t) : t;
}
function _n(e, t) {
	if (b(t)) {
		if (e.scalar == g.BYTES) return xn(t, bn);
		if (e.mapKind == "message") return xn(t, (t) => yn(e, t));
	}
	return t;
}
function vn(e, t) {
	if (Array.isArray(t)) {
		if (e.scalar == g.BYTES) return t.map(bn);
		if (e.listKind == "message") return t.map((t) => yn(e, t));
	}
	return t;
}
function yn(e, t) {
	if (e.fieldKind == "message" && !e.oneof && un(e.message)) return gn(e.message.fields[0], t);
	if (b(t)) {
		if (e.message.typeName == "google.protobuf.Struct" && e.parent.typeName !== "google.protobuf.Value") return t;
		if (!Pt(t, e.message)) return S(e.message, t);
	}
	return t;
}
function bn(e) {
	return Array.isArray(e) ? new Uint8Array(e) : e;
}
function xn(e, t) {
	let n = {};
	for (let r of Object.entries(e)) n[r[0]] = t(r[1]);
	return n;
}
var Sn = Symbol(), Cn = /* @__PURE__ */ new WeakMap();
function wn(e) {
	let t;
	if (Tn(e)) {
		let n = Cn.get(e), r, i;
		if (n) ({prototype: r, members: i} = n);
		else {
			r = {}, i = /* @__PURE__ */ new Set();
			for (let t of e.members) t.kind != "oneof" && (t.fieldKind == "scalar" || t.fieldKind == "enum") && t.presence != mn && (i.add(t), r[t.localName] = En(t));
			Cn.set(e, {
				prototype: r,
				members: i
			});
		}
		t = Object.create(r), t.$typeName = e.typeName;
		for (let n of e.members) i.has(n) || (n.kind != "field" || n.fieldKind != "message" && (n.fieldKind != "scalar" && n.fieldKind != "enum" || n.presence == mn)) && (t[n.localName] = En(n));
	} else {
		t = { $typeName: e.typeName };
		for (let n of e.members) (n.kind == "oneof" || n.presence == mn) && (t[n.localName] = En(n));
	}
	return t;
}
function Tn(e) {
	switch (e.file.edition) {
		case fn: return !1;
		case pn: return !0;
		default: return e.fields.some((e) => e.presence != mn && e.fieldKind != "message" && !e.oneof);
	}
}
function En(e) {
	if (e.kind == "oneof") return { case: void 0 };
	if (e.fieldKind == "list") return [];
	if (e.fieldKind == "map") return {};
	if (e.fieldKind == "message") return Sn;
	let t = e.getDefaultValue();
	return t === void 0 ? e.fieldKind == "scalar" ? xe(e.scalar, e.longAsString) : e.enum.values[0].number : e.fieldKind == "scalar" && e.longAsString ? t.toString() : t;
}
function Dn(e, t, n = !0) {
	return new On(e, t, n);
}
var On = class {
	get sortedFields() {
		return this._sortedFields ??= this.desc.fields.concat().sort((e, t) => e.number - t.number);
	}
	constructor(e, t, n = !0) {
		this.lists = /* @__PURE__ */ new Map(), this.maps = /* @__PURE__ */ new Map(), this.check = n, this.desc = e, this.message = this[_] = t ?? S(e), this.fields = e.fields, this.oneofs = e.oneofs, this.members = e.members;
	}
	findNumber(e) {
		return this._fieldsByNumber ||= new Map(this.desc.fields.map((e) => [e.number, e])), this._fieldsByNumber.get(e);
	}
	oneofCase(e) {
		return kn(this.message, e), we(this.message, e);
	}
	isSet(e) {
		return kn(this.message, e), Te(this.message, e);
	}
	clear(e) {
		kn(this.message, e), ke(this.message, e);
	}
	get(e) {
		kn(this.message, e);
		let t = De(this.message, e);
		switch (e.fieldKind) {
			case "list":
				let n = this.lists.get(e);
				return (!n || n[_] !== t) && this.lists.set(e, n = new An(e, t, this.check)), n;
			case "map":
				let r = this.maps.get(e);
				return (!r || r[_] !== t) && this.maps.set(e, r = new jn(e, t, this.check)), r;
			case "message": return Nn(e, t, this.check);
			case "scalar": return t === void 0 ? xe(e.scalar, !1) : Bn(e, t);
			case "enum": return t ?? e.enum.values[0].number;
		}
	}
	set(e, t) {
		if (kn(this.message, e), this.check) {
			let n = Zt(e, t);
			if (n) throw n;
		}
		let n;
		n = e.fieldKind == "message" ? Mn(e, t) : Lt(t) || It(t) ? t[_] : Vn(e, t), Oe(this.message, e, n);
	}
	getUnknown() {
		return this.message.$unknown;
	}
	setUnknown(e) {
		this.message.$unknown = e;
	}
};
function kn(e, t) {
	if (t.parent.typeName !== e.$typeName) throw new Ft(t, `cannot use ${t.toString()} with message ${e.$typeName}`, "ForeignFieldError");
}
var An = class {
	field() {
		return this._field;
	}
	get size() {
		return this._arr.length;
	}
	constructor(e, t, n) {
		this._field = e, this._arr = this[_] = t, this.check = n;
	}
	get(e) {
		let t = this._arr[e];
		return t === void 0 ? void 0 : Fn(this._field, t, this.check);
	}
	set(e, t) {
		if (e < 0 || e >= this._arr.length) throw new Ft(this._field, `list item #${e + 1}: out of range`);
		if (this.check) {
			let n = Qt(this._field, e, t);
			if (n) throw n;
		}
		this._arr[e] = Pn(this._field, t);
	}
	add(e) {
		if (this.check) {
			let t = Qt(this._field, this._arr.length, e);
			if (t) throw t;
		}
		this._arr.push(Pn(this._field, e));
	}
	clear() {
		this._arr.splice(0, this._arr.length);
	}
	[Symbol.iterator]() {
		return this.values();
	}
	keys() {
		return this._arr.keys();
	}
	*values() {
		for (let e of this._arr) yield Fn(this._field, e, this.check);
	}
	*entries() {
		for (let e = 0; e < this._arr.length; e++) yield [e, Fn(this._field, this._arr[e], this.check)];
	}
}, jn = class {
	constructor(e, t, n = !0) {
		this.obj = this[_] = t ?? {}, this.check = n, this._field = e;
	}
	field() {
		return this._field;
	}
	set(e, t) {
		if (this.check) {
			let n = $t(this._field, e, t);
			if (n) throw n;
		}
		return this.obj[Rn(e)] = In(this._field, t), this;
	}
	delete(e) {
		let t = Rn(e), n = Object.prototype.hasOwnProperty.call(this.obj, t);
		return n && delete this.obj[t], n;
	}
	clear() {
		for (let e of Object.keys(this.obj)) delete this.obj[e];
	}
	get(e) {
		let t = this.obj[Rn(e)];
		return t !== void 0 && (t = Ln(this._field, t, this.check)), t;
	}
	has(e) {
		return Object.prototype.hasOwnProperty.call(this.obj, Rn(e));
	}
	*keys() {
		for (let e of Object.keys(this.obj)) yield zn(e, this._field.mapKey);
	}
	*entries() {
		for (let e of Object.entries(this.obj)) yield [zn(e[0], this._field.mapKey), Ln(this._field, e[1], this.check)];
	}
	[Symbol.iterator]() {
		return this.entries();
	}
	get size() {
		return Object.keys(this.obj).length;
	}
	*values() {
		for (let e of Object.values(this.obj)) yield Ln(this._field, e, this.check);
	}
	forEach(e, t) {
		for (let n of this.entries()) e.call(t, n[1], n[0], this);
	}
};
function Mn(e, t) {
	return Rt(t) ? ln(t.message) && !e.oneof && e.fieldKind == "message" ? t.message.value : t.desc.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value" ? Un(t.message) : t.message : t;
}
function Nn(e, t, n) {
	return t !== void 0 && (un(e.message) && !e.oneof && e.fieldKind == "message" ? t = {
		$typeName: e.message.typeName,
		value: Bn(e.message.fields[0], t)
	} : e.message.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value" && b(t) && (t = Hn(t))), new On(e.message, t, n);
}
function Pn(e, t) {
	return e.listKind == "message" ? Mn(e, t) : Vn(e, t);
}
function Fn(e, t, n) {
	return e.listKind == "message" ? Nn(e, t, n) : Bn(e, t);
}
function In(e, t) {
	return e.mapKind == "message" ? Mn(e, t) : Vn(e, t);
}
function Ln(e, t, n) {
	return e.mapKind == "message" ? Nn(e, t, n) : t;
}
function Rn(e) {
	return typeof e == "string" || typeof e == "number" ? e : String(e);
}
function zn(e, t) {
	switch (t) {
		case g.STRING: return e;
		case g.INT32:
		case g.FIXED32:
		case g.UINT32:
		case g.SFIXED32:
		case g.SINT32: {
			let t = Number.parseInt(e);
			if (Number.isFinite(t)) return t;
			break;
		}
		case g.BOOL:
			switch (e) {
				case "true": return !0;
				case "false": return !1;
			}
			break;
		case g.UINT64:
		case g.FIXED64:
			try {
				return h.uParse(e);
			} catch {}
			break;
		default: try {
			return h.parse(e);
		} catch {}
	}
	return e;
}
function Bn(e, t) {
	switch (e.scalar) {
		case g.INT64:
		case g.SFIXED64:
		case g.SINT64:
			"longAsString" in e && e.longAsString && typeof t == "string" && (t = h.parse(t));
			break;
		case g.FIXED64:
		case g.UINT64: "longAsString" in e && e.longAsString && typeof t == "string" && (t = h.uParse(t));
	}
	return t;
}
function Vn(e, t) {
	switch (e.scalar) {
		case g.INT64:
		case g.SFIXED64:
		case g.SINT64:
			"longAsString" in e && e.longAsString ? t = String(t) : (typeof t == "string" || typeof t == "number") && (t = h.parse(t));
			break;
		case g.FIXED64:
		case g.UINT64: "longAsString" in e && e.longAsString ? t = String(t) : (typeof t == "string" || typeof t == "number") && (t = h.uParse(t));
	}
	return t;
}
function Hn(e) {
	let t = {
		$typeName: "google.protobuf.Struct",
		fields: {}
	};
	if (b(e)) for (let [n, r] of Object.entries(e)) t.fields[n] = Gn(r);
	return t;
}
function Un(e) {
	let t = {};
	for (let [n, r] of Object.entries(e.fields)) t[n] = Wn(r);
	return t;
}
function Wn(e) {
	switch (e.kind.case) {
		case "structValue": return Un(e.kind.value);
		case "listValue": return e.kind.value.values.map(Wn);
		case "nullValue":
		case void 0: return null;
		default: return e.kind.value;
	}
}
function Gn(e) {
	let t = {
		$typeName: "google.protobuf.Value",
		kind: { case: void 0 }
	};
	switch (typeof e) {
		case "number":
			t.kind = {
				case: "numberValue",
				value: e
			};
			break;
		case "string":
			t.kind = {
				case: "stringValue",
				value: e
			};
			break;
		case "boolean":
			t.kind = {
				case: "boolValue",
				value: e
			};
			break;
		case "object": if (e === null) t.kind = {
			case: "nullValue",
			value: 0
		};
		else if (Array.isArray(e)) {
			let n = {
				$typeName: "google.protobuf.ListValue",
				values: []
			};
			if (Array.isArray(e)) for (let t of e) n.values.push(Gn(t));
			t.kind = {
				case: "listValue",
				value: n
			};
		} else t.kind = {
			case: "structValue",
			value: Hn(e)
		};
	}
	return t;
}
var Kn = 3, qn = { writeUnknownFields: !0 };
function Jn(e) {
	return e ? Object.assign(Object.assign({}, qn), e) : qn;
}
function C(e, t, n) {
	return Yn(new Kt(), Jn(n), Dn(e, t)).finish();
}
function Yn(e, t, n) {
	for (let r of n.sortedFields) if (n.isSet(r)) Xn(e, t, n, r);
	else if (r.presence == Kn) throw Error(`cannot encode ${r} to binary: required field not set`);
	if (t.writeUnknownFields) for (let { no: t, wireType: r, data: i } of n.getUnknown() ?? []) e.tag(t, r).raw(i);
	return e;
}
function Xn(e, t, n, r) {
	switch (r.fieldKind) {
		case "scalar":
		case "enum":
			Zn(e, n.desc.typeName, r.name, r.scalar ?? g.INT32, r.number, n.get(r));
			break;
		case "list":
			$n(e, t, r, n.get(r));
			break;
		case "message":
			Qn(e, t, r, n.get(r));
			break;
		case "map": for (let [i, a] of n.get(r)) er(e, t, r, i, a);
	}
}
function Zn(e, t, n, r, i, a) {
	tr(e.tag(i, nr(r)), t, n, r, a);
}
function Qn(e, t, n, r) {
	n.delimitedEncoding ? Yn(e.tag(n.number, x.StartGroup), t, r).tag(n.number, x.EndGroup) : Yn(e.tag(n.number, x.LengthDelimited).fork(), t, r).join();
}
function $n(e, t, n, r) {
	if (n.listKind == "message") {
		for (let i of r) Qn(e, t, n, i);
		return;
	}
	let i = n.scalar ?? g.INT32;
	if (n.packed) {
		if (!r.size) return;
		e.tag(n.number, x.LengthDelimited).fork();
		for (let t of r) tr(e, n.parent.typeName, n.name, i, t);
		e.join();
	} else for (let t of r) Zn(e, n.parent.typeName, n.name, i, n.number, t);
}
function er(e, t, n, r, i) {
	switch (e.tag(n.number, x.LengthDelimited).fork(), Zn(e, n.parent.typeName, n.name, n.mapKey, 1, r), n.mapKind) {
		case "scalar":
		case "enum":
			Zn(e, n.parent.typeName, n.name, n.scalar ?? g.INT32, 2, i);
			break;
		case "message": Yn(e.tag(2, x.LengthDelimited).fork(), t, i).join();
	}
	e.join();
}
function tr(e, t, n, r, i) {
	try {
		switch (r) {
			case g.STRING:
				e.string(i);
				break;
			case g.BOOL:
				e.bool(i);
				break;
			case g.DOUBLE:
				e.double(i);
				break;
			case g.FLOAT:
				e.float(i);
				break;
			case g.INT32:
				e.int32(i);
				break;
			case g.INT64:
				e.int64(i);
				break;
			case g.UINT64:
				e.uint64(i);
				break;
			case g.FIXED64:
				e.fixed64(i);
				break;
			case g.BYTES:
				e.bytes(i);
				break;
			case g.FIXED32:
				e.fixed32(i);
				break;
			case g.SFIXED32:
				e.sfixed32(i);
				break;
			case g.SFIXED64:
				e.sfixed64(i);
				break;
			case g.SINT64:
				e.sint64(i);
				break;
			case g.UINT32:
				e.uint32(i);
				break;
			case g.SINT32: e.sint32(i);
		}
	} catch (e) {
		throw e instanceof Error ? Error(`cannot encode field ${t}.${n} to binary: ${e.message}`) : e;
	}
}
function nr(e) {
	switch (e) {
		case g.BYTES:
		case g.STRING: return x.LengthDelimited;
		case g.DOUBLE:
		case g.FIXED64:
		case g.SFIXED64: return x.Bit64;
		case g.FIXED32:
		case g.SFIXED32:
		case g.FLOAT: return x.Bit32;
		default: return x.Varint;
	}
}
function rr(e, t, ...n) {
	return n.reduce((e, t) => e.nestedMessages[t], e.messages[t]);
}
var ir = /* @__PURE__ */ rr(/* @__PURE__ */ St({
	name: "google/protobuf/descriptor.proto",
	package: "google.protobuf",
	messageType: [
		{
			name: "FileDescriptorSet",
			field: [{
				name: "file",
				number: 1,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.FileDescriptorProto"
			}],
			extensionRange: [{
				start: 536e6,
				end: 536000001
			}]
		},
		{
			name: "FileDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "package",
					number: 2,
					type: 9,
					label: 1
				},
				{
					name: "dependency",
					number: 3,
					type: 9,
					label: 3
				},
				{
					name: "public_dependency",
					number: 10,
					type: 5,
					label: 3
				},
				{
					name: "weak_dependency",
					number: 11,
					type: 5,
					label: 3
				},
				{
					name: "option_dependency",
					number: 15,
					type: 9,
					label: 3
				},
				{
					name: "message_type",
					number: 4,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto"
				},
				{
					name: "enum_type",
					number: 5,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumDescriptorProto"
				},
				{
					name: "service",
					number: 6,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.ServiceDescriptorProto"
				},
				{
					name: "extension",
					number: 7,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldDescriptorProto"
				},
				{
					name: "options",
					number: 8,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FileOptions"
				},
				{
					name: "source_code_info",
					number: 9,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.SourceCodeInfo"
				},
				{
					name: "syntax",
					number: 12,
					type: 9,
					label: 1
				},
				{
					name: "edition",
					number: 14,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				}
			]
		},
		{
			name: "DescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "field",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldDescriptorProto"
				},
				{
					name: "extension",
					number: 6,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldDescriptorProto"
				},
				{
					name: "nested_type",
					number: 3,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto"
				},
				{
					name: "enum_type",
					number: 4,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumDescriptorProto"
				},
				{
					name: "extension_range",
					number: 5,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto.ExtensionRange"
				},
				{
					name: "oneof_decl",
					number: 8,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.OneofDescriptorProto"
				},
				{
					name: "options",
					number: 7,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.MessageOptions"
				},
				{
					name: "reserved_range",
					number: 9,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto.ReservedRange"
				},
				{
					name: "reserved_name",
					number: 10,
					type: 9,
					label: 3
				},
				{
					name: "visibility",
					number: 11,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.SymbolVisibility"
				}
			],
			nestedType: [{
				name: "ExtensionRange",
				field: [
					{
						name: "start",
						number: 1,
						type: 5,
						label: 1
					},
					{
						name: "end",
						number: 2,
						type: 5,
						label: 1
					},
					{
						name: "options",
						number: 3,
						type: 11,
						label: 1,
						typeName: ".google.protobuf.ExtensionRangeOptions"
					}
				]
			}, {
				name: "ReservedRange",
				field: [{
					name: "start",
					number: 1,
					type: 5,
					label: 1
				}, {
					name: "end",
					number: 2,
					type: 5,
					label: 1
				}]
			}]
		},
		{
			name: "ExtensionRangeOptions",
			field: [
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				},
				{
					name: "declaration",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.ExtensionRangeOptions.Declaration",
					options: { retention: 2 }
				},
				{
					name: "features",
					number: 50,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "verification",
					number: 3,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.ExtensionRangeOptions.VerificationState",
					defaultValue: "UNVERIFIED",
					options: { retention: 2 }
				}
			],
			nestedType: [{
				name: "Declaration",
				field: [
					{
						name: "number",
						number: 1,
						type: 5,
						label: 1
					},
					{
						name: "full_name",
						number: 2,
						type: 9,
						label: 1
					},
					{
						name: "type",
						number: 3,
						type: 9,
						label: 1
					},
					{
						name: "reserved",
						number: 5,
						type: 8,
						label: 1
					},
					{
						name: "repeated",
						number: 6,
						type: 8,
						label: 1
					}
				]
			}],
			enumType: [{
				name: "VerificationState",
				value: [{
					name: "DECLARATION",
					number: 0
				}, {
					name: "UNVERIFIED",
					number: 1
				}]
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "FieldDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "number",
					number: 3,
					type: 5,
					label: 1
				},
				{
					name: "label",
					number: 4,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldDescriptorProto.Label"
				},
				{
					name: "type",
					number: 5,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldDescriptorProto.Type"
				},
				{
					name: "type_name",
					number: 6,
					type: 9,
					label: 1
				},
				{
					name: "extendee",
					number: 2,
					type: 9,
					label: 1
				},
				{
					name: "default_value",
					number: 7,
					type: 9,
					label: 1
				},
				{
					name: "oneof_index",
					number: 9,
					type: 5,
					label: 1
				},
				{
					name: "json_name",
					number: 10,
					type: 9,
					label: 1
				},
				{
					name: "options",
					number: 8,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FieldOptions"
				},
				{
					name: "proto3_optional",
					number: 17,
					type: 8,
					label: 1
				}
			],
			enumType: [{
				name: "Type",
				value: [
					{
						name: "TYPE_DOUBLE",
						number: 1
					},
					{
						name: "TYPE_FLOAT",
						number: 2
					},
					{
						name: "TYPE_INT64",
						number: 3
					},
					{
						name: "TYPE_UINT64",
						number: 4
					},
					{
						name: "TYPE_INT32",
						number: 5
					},
					{
						name: "TYPE_FIXED64",
						number: 6
					},
					{
						name: "TYPE_FIXED32",
						number: 7
					},
					{
						name: "TYPE_BOOL",
						number: 8
					},
					{
						name: "TYPE_STRING",
						number: 9
					},
					{
						name: "TYPE_GROUP",
						number: 10
					},
					{
						name: "TYPE_MESSAGE",
						number: 11
					},
					{
						name: "TYPE_BYTES",
						number: 12
					},
					{
						name: "TYPE_UINT32",
						number: 13
					},
					{
						name: "TYPE_ENUM",
						number: 14
					},
					{
						name: "TYPE_SFIXED32",
						number: 15
					},
					{
						name: "TYPE_SFIXED64",
						number: 16
					},
					{
						name: "TYPE_SINT32",
						number: 17
					},
					{
						name: "TYPE_SINT64",
						number: 18
					}
				]
			}, {
				name: "Label",
				value: [
					{
						name: "LABEL_OPTIONAL",
						number: 1
					},
					{
						name: "LABEL_REPEATED",
						number: 3
					},
					{
						name: "LABEL_REQUIRED",
						number: 2
					}
				]
			}]
		},
		{
			name: "OneofDescriptorProto",
			field: [{
				name: "name",
				number: 1,
				type: 9,
				label: 1
			}, {
				name: "options",
				number: 2,
				type: 11,
				label: 1,
				typeName: ".google.protobuf.OneofOptions"
			}]
		},
		{
			name: "EnumDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "value",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumValueDescriptorProto"
				},
				{
					name: "options",
					number: 3,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.EnumOptions"
				},
				{
					name: "reserved_range",
					number: 4,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumDescriptorProto.EnumReservedRange"
				},
				{
					name: "reserved_name",
					number: 5,
					type: 9,
					label: 3
				},
				{
					name: "visibility",
					number: 6,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.SymbolVisibility"
				}
			],
			nestedType: [{
				name: "EnumReservedRange",
				field: [{
					name: "start",
					number: 1,
					type: 5,
					label: 1
				}, {
					name: "end",
					number: 2,
					type: 5,
					label: 1
				}]
			}]
		},
		{
			name: "EnumValueDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "number",
					number: 2,
					type: 5,
					label: 1
				},
				{
					name: "options",
					number: 3,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.EnumValueOptions"
				}
			]
		},
		{
			name: "ServiceDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "method",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.MethodDescriptorProto"
				},
				{
					name: "options",
					number: 3,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.ServiceOptions"
				}
			]
		},
		{
			name: "MethodDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "input_type",
					number: 2,
					type: 9,
					label: 1
				},
				{
					name: "output_type",
					number: 3,
					type: 9,
					label: 1
				},
				{
					name: "options",
					number: 4,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.MethodOptions"
				},
				{
					name: "client_streaming",
					number: 5,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "server_streaming",
					number: 6,
					type: 8,
					label: 1,
					defaultValue: "false"
				}
			]
		},
		{
			name: "FileOptions",
			field: [
				{
					name: "java_package",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "java_outer_classname",
					number: 8,
					type: 9,
					label: 1
				},
				{
					name: "java_multiple_files",
					number: 10,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "java_generate_equals_and_hash",
					number: 20,
					type: 8,
					label: 1,
					options: { deprecated: !0 }
				},
				{
					name: "java_string_check_utf8",
					number: 27,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "optimize_for",
					number: 9,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FileOptions.OptimizeMode",
					defaultValue: "SPEED"
				},
				{
					name: "go_package",
					number: 11,
					type: 9,
					label: 1
				},
				{
					name: "cc_generic_services",
					number: 16,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "java_generic_services",
					number: 17,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "py_generic_services",
					number: 18,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated",
					number: 23,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "cc_enable_arenas",
					number: 31,
					type: 8,
					label: 1,
					defaultValue: "true"
				},
				{
					name: "objc_class_prefix",
					number: 36,
					type: 9,
					label: 1
				},
				{
					name: "csharp_namespace",
					number: 37,
					type: 9,
					label: 1
				},
				{
					name: "swift_prefix",
					number: 39,
					type: 9,
					label: 1
				},
				{
					name: "php_class_prefix",
					number: 40,
					type: 9,
					label: 1
				},
				{
					name: "php_namespace",
					number: 41,
					type: 9,
					label: 1
				},
				{
					name: "php_metadata_namespace",
					number: 44,
					type: 9,
					label: 1
				},
				{
					name: "ruby_package",
					number: 45,
					type: 9,
					label: 1
				},
				{
					name: "features",
					number: 50,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			enumType: [{
				name: "OptimizeMode",
				value: [
					{
						name: "SPEED",
						number: 1
					},
					{
						name: "CODE_SIZE",
						number: 2
					},
					{
						name: "LITE_RUNTIME",
						number: 3
					}
				]
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "MessageOptions",
			field: [
				{
					name: "message_set_wire_format",
					number: 1,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "no_standard_descriptor_accessor",
					number: 2,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "map_entry",
					number: 7,
					type: 8,
					label: 1
				},
				{
					name: "deprecated_legacy_json_field_conflicts",
					number: 11,
					type: 8,
					label: 1,
					options: { deprecated: !0 }
				},
				{
					name: "features",
					number: 12,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "FieldOptions",
			field: [
				{
					name: "ctype",
					number: 1,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.CType",
					defaultValue: "STRING"
				},
				{
					name: "packed",
					number: 2,
					type: 8,
					label: 1
				},
				{
					name: "jstype",
					number: 6,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.JSType",
					defaultValue: "JS_NORMAL"
				},
				{
					name: "lazy",
					number: 5,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "unverified_lazy",
					number: 15,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "weak",
					number: 10,
					type: 8,
					label: 1,
					defaultValue: "false",
					options: { deprecated: !0 }
				},
				{
					name: "debug_redact",
					number: 16,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "retention",
					number: 17,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.OptionRetention"
				},
				{
					name: "targets",
					number: 19,
					type: 14,
					label: 3,
					typeName: ".google.protobuf.FieldOptions.OptionTargetType"
				},
				{
					name: "edition_defaults",
					number: 20,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldOptions.EditionDefault"
				},
				{
					name: "features",
					number: 21,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "feature_support",
					number: 22,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.FeatureSupport"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			nestedType: [{
				name: "EditionDefault",
				field: [{
					name: "edition",
					number: 3,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				}, {
					name: "value",
					number: 2,
					type: 9,
					label: 1
				}]
			}, {
				name: "FeatureSupport",
				field: [
					{
						name: "edition_introduced",
						number: 1,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					},
					{
						name: "edition_deprecated",
						number: 2,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					},
					{
						name: "deprecation_warning",
						number: 3,
						type: 9,
						label: 1
					},
					{
						name: "edition_removed",
						number: 4,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					}
				]
			}],
			enumType: [
				{
					name: "CType",
					value: [
						{
							name: "STRING",
							number: 0
						},
						{
							name: "CORD",
							number: 1
						},
						{
							name: "STRING_PIECE",
							number: 2
						}
					]
				},
				{
					name: "JSType",
					value: [
						{
							name: "JS_NORMAL",
							number: 0
						},
						{
							name: "JS_STRING",
							number: 1
						},
						{
							name: "JS_NUMBER",
							number: 2
						}
					]
				},
				{
					name: "OptionRetention",
					value: [
						{
							name: "RETENTION_UNKNOWN",
							number: 0
						},
						{
							name: "RETENTION_RUNTIME",
							number: 1
						},
						{
							name: "RETENTION_SOURCE",
							number: 2
						}
					]
				},
				{
					name: "OptionTargetType",
					value: [
						{
							name: "TARGET_TYPE_UNKNOWN",
							number: 0
						},
						{
							name: "TARGET_TYPE_FILE",
							number: 1
						},
						{
							name: "TARGET_TYPE_EXTENSION_RANGE",
							number: 2
						},
						{
							name: "TARGET_TYPE_MESSAGE",
							number: 3
						},
						{
							name: "TARGET_TYPE_FIELD",
							number: 4
						},
						{
							name: "TARGET_TYPE_ONEOF",
							number: 5
						},
						{
							name: "TARGET_TYPE_ENUM",
							number: 6
						},
						{
							name: "TARGET_TYPE_ENUM_ENTRY",
							number: 7
						},
						{
							name: "TARGET_TYPE_SERVICE",
							number: 8
						},
						{
							name: "TARGET_TYPE_METHOD",
							number: 9
						}
					]
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "OneofOptions",
			field: [{
				name: "features",
				number: 1,
				type: 11,
				label: 1,
				typeName: ".google.protobuf.FeatureSet"
			}, {
				name: "uninterpreted_option",
				number: 999,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.UninterpretedOption"
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "EnumOptions",
			field: [
				{
					name: "allow_alias",
					number: 2,
					type: 8,
					label: 1
				},
				{
					name: "deprecated",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated_legacy_json_field_conflicts",
					number: 6,
					type: 8,
					label: 1,
					options: { deprecated: !0 }
				},
				{
					name: "features",
					number: 7,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "EnumValueOptions",
			field: [
				{
					name: "deprecated",
					number: 1,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "features",
					number: 2,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "debug_redact",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "feature_support",
					number: 4,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.FeatureSupport"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "ServiceOptions",
			field: [
				{
					name: "features",
					number: 34,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "deprecated",
					number: 33,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "MethodOptions",
			field: [
				{
					name: "deprecated",
					number: 33,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "idempotency_level",
					number: 34,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.MethodOptions.IdempotencyLevel",
					defaultValue: "IDEMPOTENCY_UNKNOWN"
				},
				{
					name: "features",
					number: 35,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			enumType: [{
				name: "IdempotencyLevel",
				value: [
					{
						name: "IDEMPOTENCY_UNKNOWN",
						number: 0
					},
					{
						name: "NO_SIDE_EFFECTS",
						number: 1
					},
					{
						name: "IDEMPOTENT",
						number: 2
					}
				]
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "UninterpretedOption",
			field: [
				{
					name: "name",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption.NamePart"
				},
				{
					name: "identifier_value",
					number: 3,
					type: 9,
					label: 1
				},
				{
					name: "positive_int_value",
					number: 4,
					type: 4,
					label: 1
				},
				{
					name: "negative_int_value",
					number: 5,
					type: 3,
					label: 1
				},
				{
					name: "double_value",
					number: 6,
					type: 1,
					label: 1
				},
				{
					name: "string_value",
					number: 7,
					type: 12,
					label: 1
				},
				{
					name: "aggregate_value",
					number: 8,
					type: 9,
					label: 1
				}
			],
			nestedType: [{
				name: "NamePart",
				field: [{
					name: "name_part",
					number: 1,
					type: 9,
					label: 2
				}, {
					name: "is_extension",
					number: 2,
					type: 8,
					label: 2
				}]
			}]
		},
		{
			name: "FeatureSet",
			field: [
				{
					name: "field_presence",
					number: 1,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.FieldPresence",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [
							{
								value: "EXPLICIT",
								edition: 900
							},
							{
								value: "IMPLICIT",
								edition: 999
							},
							{
								value: "EXPLICIT",
								edition: 1e3
							}
						]
					}
				},
				{
					name: "enum_type",
					number: 2,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.EnumType",
					options: {
						retention: 1,
						targets: [6, 1],
						editionDefaults: [{
							value: "CLOSED",
							edition: 900
						}, {
							value: "OPEN",
							edition: 999
						}]
					}
				},
				{
					name: "repeated_field_encoding",
					number: 3,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.RepeatedFieldEncoding",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [{
							value: "EXPANDED",
							edition: 900
						}, {
							value: "PACKED",
							edition: 999
						}]
					}
				},
				{
					name: "utf8_validation",
					number: 4,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.Utf8Validation",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [{
							value: "NONE",
							edition: 900
						}, {
							value: "VERIFY",
							edition: 999
						}]
					}
				},
				{
					name: "message_encoding",
					number: 5,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.MessageEncoding",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [{
							value: "LENGTH_PREFIXED",
							edition: 900
						}]
					}
				},
				{
					name: "json_format",
					number: 6,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.JsonFormat",
					options: {
						retention: 1,
						targets: [
							3,
							6,
							1
						],
						editionDefaults: [{
							value: "LEGACY_BEST_EFFORT",
							edition: 900
						}, {
							value: "ALLOW",
							edition: 999
						}]
					}
				},
				{
					name: "enforce_naming_style",
					number: 7,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.EnforceNamingStyle",
					options: {
						retention: 2,
						targets: [
							1,
							2,
							3,
							4,
							5,
							6,
							7,
							8,
							9
						],
						editionDefaults: [{
							value: "STYLE_LEGACY",
							edition: 900
						}, {
							value: "STYLE2024",
							edition: 1001
						}]
					}
				},
				{
					name: "default_symbol_visibility",
					number: 8,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.VisibilityFeature.DefaultSymbolVisibility",
					options: {
						retention: 2,
						targets: [1],
						editionDefaults: [{
							value: "EXPORT_ALL",
							edition: 900
						}, {
							value: "EXPORT_TOP_LEVEL",
							edition: 1001
						}]
					}
				}
			],
			nestedType: [{
				name: "VisibilityFeature",
				enumType: [{
					name: "DefaultSymbolVisibility",
					value: [
						{
							name: "DEFAULT_SYMBOL_VISIBILITY_UNKNOWN",
							number: 0
						},
						{
							name: "EXPORT_ALL",
							number: 1
						},
						{
							name: "EXPORT_TOP_LEVEL",
							number: 2
						},
						{
							name: "LOCAL_ALL",
							number: 3
						},
						{
							name: "STRICT",
							number: 4
						}
					]
				}]
			}],
			enumType: [
				{
					name: "FieldPresence",
					value: [
						{
							name: "FIELD_PRESENCE_UNKNOWN",
							number: 0
						},
						{
							name: "EXPLICIT",
							number: 1
						},
						{
							name: "IMPLICIT",
							number: 2
						},
						{
							name: "LEGACY_REQUIRED",
							number: 3
						}
					]
				},
				{
					name: "EnumType",
					value: [
						{
							name: "ENUM_TYPE_UNKNOWN",
							number: 0
						},
						{
							name: "OPEN",
							number: 1
						},
						{
							name: "CLOSED",
							number: 2
						}
					]
				},
				{
					name: "RepeatedFieldEncoding",
					value: [
						{
							name: "REPEATED_FIELD_ENCODING_UNKNOWN",
							number: 0
						},
						{
							name: "PACKED",
							number: 1
						},
						{
							name: "EXPANDED",
							number: 2
						}
					]
				},
				{
					name: "Utf8Validation",
					value: [
						{
							name: "UTF8_VALIDATION_UNKNOWN",
							number: 0
						},
						{
							name: "VERIFY",
							number: 2
						},
						{
							name: "NONE",
							number: 3
						}
					]
				},
				{
					name: "MessageEncoding",
					value: [
						{
							name: "MESSAGE_ENCODING_UNKNOWN",
							number: 0
						},
						{
							name: "LENGTH_PREFIXED",
							number: 1
						},
						{
							name: "DELIMITED",
							number: 2
						}
					]
				},
				{
					name: "JsonFormat",
					value: [
						{
							name: "JSON_FORMAT_UNKNOWN",
							number: 0
						},
						{
							name: "ALLOW",
							number: 1
						},
						{
							name: "LEGACY_BEST_EFFORT",
							number: 2
						}
					]
				},
				{
					name: "EnforceNamingStyle",
					value: [
						{
							name: "ENFORCE_NAMING_STYLE_UNKNOWN",
							number: 0
						},
						{
							name: "STYLE2024",
							number: 1
						},
						{
							name: "STYLE_LEGACY",
							number: 2
						}
					]
				}
			],
			extensionRange: [
				{
					start: 1e3,
					end: 9995
				},
				{
					start: 9995,
					end: 1e4
				},
				{
					start: 1e4,
					end: 10001
				}
			]
		},
		{
			name: "FeatureSetDefaults",
			field: [
				{
					name: "defaults",
					number: 1,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FeatureSetDefaults.FeatureSetEditionDefault"
				},
				{
					name: "minimum_edition",
					number: 4,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				},
				{
					name: "maximum_edition",
					number: 5,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				}
			],
			nestedType: [{
				name: "FeatureSetEditionDefault",
				field: [
					{
						name: "edition",
						number: 3,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					},
					{
						name: "overridable_features",
						number: 4,
						type: 11,
						label: 1,
						typeName: ".google.protobuf.FeatureSet"
					},
					{
						name: "fixed_features",
						number: 5,
						type: 11,
						label: 1,
						typeName: ".google.protobuf.FeatureSet"
					}
				]
			}]
		},
		{
			name: "SourceCodeInfo",
			field: [{
				name: "location",
				number: 1,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.SourceCodeInfo.Location"
			}],
			nestedType: [{
				name: "Location",
				field: [
					{
						name: "path",
						number: 1,
						type: 5,
						label: 3,
						options: { packed: !0 }
					},
					{
						name: "span",
						number: 2,
						type: 5,
						label: 3,
						options: { packed: !0 }
					},
					{
						name: "leading_comments",
						number: 3,
						type: 9,
						label: 1
					},
					{
						name: "trailing_comments",
						number: 4,
						type: 9,
						label: 1
					},
					{
						name: "leading_detached_comments",
						number: 6,
						type: 9,
						label: 3
					}
				]
			}],
			extensionRange: [{
				start: 536e6,
				end: 536000001
			}]
		},
		{
			name: "GeneratedCodeInfo",
			field: [{
				name: "annotation",
				number: 1,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.GeneratedCodeInfo.Annotation"
			}],
			nestedType: [{
				name: "Annotation",
				field: [
					{
						name: "path",
						number: 1,
						type: 5,
						label: 3,
						options: { packed: !0 }
					},
					{
						name: "source_file",
						number: 2,
						type: 9,
						label: 1
					},
					{
						name: "begin",
						number: 3,
						type: 5,
						label: 1
					},
					{
						name: "end",
						number: 4,
						type: 5,
						label: 1
					},
					{
						name: "semantic",
						number: 5,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.GeneratedCodeInfo.Annotation.Semantic"
					}
				],
				enumType: [{
					name: "Semantic",
					value: [
						{
							name: "NONE",
							number: 0
						},
						{
							name: "SET",
							number: 1
						},
						{
							name: "ALIAS",
							number: 2
						}
					]
				}]
			}]
		}
	],
	enumType: [{
		name: "Edition",
		value: [
			{
				name: "EDITION_UNKNOWN",
				number: 0
			},
			{
				name: "EDITION_LEGACY",
				number: 900
			},
			{
				name: "EDITION_PROTO2",
				number: 998
			},
			{
				name: "EDITION_PROTO3",
				number: 999
			},
			{
				name: "EDITION_2023",
				number: 1e3
			},
			{
				name: "EDITION_2024",
				number: 1001
			},
			{
				name: "EDITION_1_TEST_ONLY",
				number: 1
			},
			{
				name: "EDITION_2_TEST_ONLY",
				number: 2
			},
			{
				name: "EDITION_99997_TEST_ONLY",
				number: 99997
			},
			{
				name: "EDITION_99998_TEST_ONLY",
				number: 99998
			},
			{
				name: "EDITION_99999_TEST_ONLY",
				number: 99999
			},
			{
				name: "EDITION_MAX",
				number: 2147483647
			}
		]
	}, {
		name: "SymbolVisibility",
		value: [
			{
				name: "VISIBILITY_UNSET",
				number: 0
			},
			{
				name: "VISIBILITY_LOCAL",
				number: 1
			},
			{
				name: "VISIBILITY_EXPORT",
				number: 2
			}
		]
	}]
}), 1), ar;
(function(e) {
	e[e.DECLARATION = 0] = "DECLARATION", e[e.UNVERIFIED = 1] = "UNVERIFIED";
})(ar ||= {});
var or;
(function(e) {
	e[e.DOUBLE = 1] = "DOUBLE", e[e.FLOAT = 2] = "FLOAT", e[e.INT64 = 3] = "INT64", e[e.UINT64 = 4] = "UINT64", e[e.INT32 = 5] = "INT32", e[e.FIXED64 = 6] = "FIXED64", e[e.FIXED32 = 7] = "FIXED32", e[e.BOOL = 8] = "BOOL", e[e.STRING = 9] = "STRING", e[e.GROUP = 10] = "GROUP", e[e.MESSAGE = 11] = "MESSAGE", e[e.BYTES = 12] = "BYTES", e[e.UINT32 = 13] = "UINT32", e[e.ENUM = 14] = "ENUM", e[e.SFIXED32 = 15] = "SFIXED32", e[e.SFIXED64 = 16] = "SFIXED64", e[e.SINT32 = 17] = "SINT32", e[e.SINT64 = 18] = "SINT64";
})(or ||= {});
var sr;
(function(e) {
	e[e.OPTIONAL = 1] = "OPTIONAL", e[e.REPEATED = 3] = "REPEATED", e[e.REQUIRED = 2] = "REQUIRED";
})(sr ||= {});
var cr;
(function(e) {
	e[e.SPEED = 1] = "SPEED", e[e.CODE_SIZE = 2] = "CODE_SIZE", e[e.LITE_RUNTIME = 3] = "LITE_RUNTIME";
})(cr ||= {});
var lr;
(function(e) {
	e[e.STRING = 0] = "STRING", e[e.CORD = 1] = "CORD", e[e.STRING_PIECE = 2] = "STRING_PIECE";
})(lr ||= {});
var ur;
(function(e) {
	e[e.JS_NORMAL = 0] = "JS_NORMAL", e[e.JS_STRING = 1] = "JS_STRING", e[e.JS_NUMBER = 2] = "JS_NUMBER";
})(ur ||= {});
var dr;
(function(e) {
	e[e.RETENTION_UNKNOWN = 0] = "RETENTION_UNKNOWN", e[e.RETENTION_RUNTIME = 1] = "RETENTION_RUNTIME", e[e.RETENTION_SOURCE = 2] = "RETENTION_SOURCE";
})(dr ||= {});
var fr;
(function(e) {
	e[e.TARGET_TYPE_UNKNOWN = 0] = "TARGET_TYPE_UNKNOWN", e[e.TARGET_TYPE_FILE = 1] = "TARGET_TYPE_FILE", e[e.TARGET_TYPE_EXTENSION_RANGE = 2] = "TARGET_TYPE_EXTENSION_RANGE", e[e.TARGET_TYPE_MESSAGE = 3] = "TARGET_TYPE_MESSAGE", e[e.TARGET_TYPE_FIELD = 4] = "TARGET_TYPE_FIELD", e[e.TARGET_TYPE_ONEOF = 5] = "TARGET_TYPE_ONEOF", e[e.TARGET_TYPE_ENUM = 6] = "TARGET_TYPE_ENUM", e[e.TARGET_TYPE_ENUM_ENTRY = 7] = "TARGET_TYPE_ENUM_ENTRY", e[e.TARGET_TYPE_SERVICE = 8] = "TARGET_TYPE_SERVICE", e[e.TARGET_TYPE_METHOD = 9] = "TARGET_TYPE_METHOD";
})(fr ||= {});
var pr;
(function(e) {
	e[e.IDEMPOTENCY_UNKNOWN = 0] = "IDEMPOTENCY_UNKNOWN", e[e.NO_SIDE_EFFECTS = 1] = "NO_SIDE_EFFECTS", e[e.IDEMPOTENT = 2] = "IDEMPOTENT";
})(pr ||= {});
var mr;
(function(e) {
	e[e.DEFAULT_SYMBOL_VISIBILITY_UNKNOWN = 0] = "DEFAULT_SYMBOL_VISIBILITY_UNKNOWN", e[e.EXPORT_ALL = 1] = "EXPORT_ALL", e[e.EXPORT_TOP_LEVEL = 2] = "EXPORT_TOP_LEVEL", e[e.LOCAL_ALL = 3] = "LOCAL_ALL", e[e.STRICT = 4] = "STRICT";
})(mr ||= {});
var hr;
(function(e) {
	e[e.FIELD_PRESENCE_UNKNOWN = 0] = "FIELD_PRESENCE_UNKNOWN", e[e.EXPLICIT = 1] = "EXPLICIT", e[e.IMPLICIT = 2] = "IMPLICIT", e[e.LEGACY_REQUIRED = 3] = "LEGACY_REQUIRED";
})(hr ||= {});
var gr;
(function(e) {
	e[e.ENUM_TYPE_UNKNOWN = 0] = "ENUM_TYPE_UNKNOWN", e[e.OPEN = 1] = "OPEN", e[e.CLOSED = 2] = "CLOSED";
})(gr ||= {});
var _r;
(function(e) {
	e[e.REPEATED_FIELD_ENCODING_UNKNOWN = 0] = "REPEATED_FIELD_ENCODING_UNKNOWN", e[e.PACKED = 1] = "PACKED", e[e.EXPANDED = 2] = "EXPANDED";
})(_r ||= {});
var vr;
(function(e) {
	e[e.UTF8_VALIDATION_UNKNOWN = 0] = "UTF8_VALIDATION_UNKNOWN", e[e.VERIFY = 2] = "VERIFY", e[e.NONE = 3] = "NONE";
})(vr ||= {});
var yr;
(function(e) {
	e[e.MESSAGE_ENCODING_UNKNOWN = 0] = "MESSAGE_ENCODING_UNKNOWN", e[e.LENGTH_PREFIXED = 1] = "LENGTH_PREFIXED", e[e.DELIMITED = 2] = "DELIMITED";
})(yr ||= {});
var br;
(function(e) {
	e[e.JSON_FORMAT_UNKNOWN = 0] = "JSON_FORMAT_UNKNOWN", e[e.ALLOW = 1] = "ALLOW", e[e.LEGACY_BEST_EFFORT = 2] = "LEGACY_BEST_EFFORT";
})(br ||= {});
var xr;
(function(e) {
	e[e.ENFORCE_NAMING_STYLE_UNKNOWN = 0] = "ENFORCE_NAMING_STYLE_UNKNOWN", e[e.STYLE2024 = 1] = "STYLE2024", e[e.STYLE_LEGACY = 2] = "STYLE_LEGACY";
})(xr ||= {});
var Sr;
(function(e) {
	e[e.NONE = 0] = "NONE", e[e.SET = 1] = "SET", e[e.ALIAS = 2] = "ALIAS";
})(Sr ||= {});
var Cr;
(function(e) {
	e[e.EDITION_UNKNOWN = 0] = "EDITION_UNKNOWN", e[e.EDITION_LEGACY = 900] = "EDITION_LEGACY", e[e.EDITION_PROTO2 = 998] = "EDITION_PROTO2", e[e.EDITION_PROTO3 = 999] = "EDITION_PROTO3", e[e.EDITION_2023 = 1e3] = "EDITION_2023", e[e.EDITION_2024 = 1001] = "EDITION_2024", e[e.EDITION_1_TEST_ONLY = 1] = "EDITION_1_TEST_ONLY", e[e.EDITION_2_TEST_ONLY = 2] = "EDITION_2_TEST_ONLY", e[e.EDITION_99997_TEST_ONLY = 99997] = "EDITION_99997_TEST_ONLY", e[e.EDITION_99998_TEST_ONLY = 99998] = "EDITION_99998_TEST_ONLY", e[e.EDITION_99999_TEST_ONLY = 99999] = "EDITION_99999_TEST_ONLY", e[e.EDITION_MAX = 2147483647] = "EDITION_MAX";
})(Cr ||= {});
var wr;
(function(e) {
	e[e.VISIBILITY_UNSET = 0] = "VISIBILITY_UNSET", e[e.VISIBILITY_LOCAL = 1] = "VISIBILITY_LOCAL", e[e.VISIBILITY_EXPORT = 2] = "VISIBILITY_EXPORT";
})(wr ||= {});
function w(e, t, ...n) {
	if (n.length == 0) return e.enums[t];
	let r = n.pop();
	return n.reduce((e, t) => e.nestedMessages[t], e.messages[t]).nestedEnums[r];
}
var Tr = { readUnknownFields: !0 };
function Er(e) {
	return e ? Object.assign(Object.assign({}, Tr), e) : Tr;
}
function T(e, t, n) {
	let r = Dn(e, void 0, !1);
	return Dr(r, new qt(t), Er(n), !1, t.byteLength), r.message;
}
function Dr(e, t, n, r, i) {
	let a = r ? t.len : t.pos + i, o, s, c = e.getUnknown() ?? [];
	for (; t.pos < a && ([o, s] = t.tag(), !(r && s == x.EndGroup));) {
		let r = e.findNumber(o);
		if (r) Or(e, t, r, s, n);
		else {
			let e = t.skip(s, o);
			n.readUnknownFields && c.push({
				no: o,
				wireType: s,
				data: e
			});
		}
	}
	if (r && (s != x.EndGroup || o !== i)) throw Error("invalid end group tag");
	c.length > 0 && e.setUnknown(c);
}
function Or(e, t, n, r, i) {
	switch (n.fieldKind) {
		case "scalar":
			e.set(n, Mr(t, n.scalar));
			break;
		case "enum":
			let a = Mr(t, g.INT32);
			if (n.enum.open) e.set(n, a);
			else if (n.enum.values.some((e) => e.number === a)) e.set(n, a);
			else if (i.readUnknownFields) {
				let t = [];
				ge(a, t);
				let i = e.getUnknown() ?? [];
				i.push({
					no: n.number,
					wireType: r,
					data: new Uint8Array(t)
				}), e.setUnknown(i);
			}
			break;
		case "message":
			e.set(n, jr(t, i, n, e.get(n)));
			break;
		case "list":
			Ar(t, r, e.get(n), i);
			break;
		case "map": kr(t, e.get(n), i);
	}
}
function kr(e, t, n) {
	let r = t.field(), i, a, o = e.uint32(), s = e.pos + o;
	for (; e.pos < s;) {
		let [t] = e.tag();
		switch (t) {
			case 1:
				i = Mr(e, r.mapKey);
				break;
			case 2: switch (r.mapKind) {
				case "scalar":
					a = Mr(e, r.scalar);
					break;
				case "enum":
					a = e.int32();
					break;
				case "message": a = jr(e, n, r);
			}
		}
	}
	if (i === void 0 && (i = xe(r.mapKey, !1)), a === void 0) switch (r.mapKind) {
		case "scalar":
			a = xe(r.scalar, !1);
			break;
		case "enum":
			a = r.enum.values[0].number;
			break;
		case "message": a = Dn(r.message, void 0, !1);
	}
	t.set(i, a);
}
function Ar(e, t, n, r) {
	let i = n.field();
	if (i.listKind === "message") {
		n.add(jr(e, r, i));
		return;
	}
	let a = i.scalar ?? g.INT32;
	if (t != x.LengthDelimited || a == g.STRING || a == g.BYTES) {
		n.add(Mr(e, a));
		return;
	}
	let o = e.uint32() + e.pos;
	for (; e.pos < o;) n.add(Mr(e, a));
}
function jr(e, t, n, r) {
	let i = n.delimitedEncoding, a = r ?? Dn(n.message, void 0, !1);
	return Dr(a, e, t, i, i ? n.number : e.uint32()), a;
}
function Mr(e, t) {
	switch (t) {
		case g.STRING: return e.string();
		case g.BOOL: return e.bool();
		case g.DOUBLE: return e.double();
		case g.FLOAT: return e.float();
		case g.INT32: return e.int32();
		case g.INT64: return e.int64();
		case g.UINT64: return e.uint64();
		case g.FIXED64: return e.fixed64();
		case g.BYTES: return e.bytes();
		case g.FIXED32: return e.fixed32();
		case g.SFIXED32: return e.sfixed32();
		case g.SFIXED64: return e.sfixed64();
		case g.SINT64: return e.sint64();
		case g.UINT32: return e.uint32();
		case g.SINT32: return e.sint32();
	}
}
function E(e, t) {
	let n = T(ir, Ot(e));
	return n.messageType.forEach(Ae), n.dependency = t?.map((e) => e.proto.name) ?? [], Fe(n, (e) => t?.find((t) => t.proto.name === e)).getFile(n.name);
}
function D(e, t, ...n) {
	return n.reduce((e, t) => e.nestedMessages[t], e.messages[t]);
}
var O = s({
	ATAK: () => Wo,
	Admin: () => R,
	AppOnly: () => Vo,
	CannedMessages: () => ts,
	Channel: () => Nr,
	ClientOnly: () => cs,
	Config: () => Br,
	ConnectionStatus: () => Oi,
	LocalOnly: () => is,
	Mesh: () => I,
	ModuleConfig: () => Fi,
	Mqtt: () => ds,
	PaxCount: () => hs,
	Portnums: () => N,
	PowerMon: () => vs,
	RemoteHardware: () => Es,
	Rtttl: () => js,
	StoreForward: () => Ps,
	Telemetry: () => pa,
	Xmodem: () => F
}), Nr = m({
	ChannelSchema: () => Ir,
	ChannelSettingsSchema: () => Pr,
	Channel_Role: () => Lr,
	Channel_RoleSchema: () => Rr,
	ModuleSettingsSchema: () => Fr,
	file_channel: () => k
}), k = /* @__PURE__ */ E("Cg1jaGFubmVsLnByb3RvEgptZXNodGFzdGljIrgBCg9DaGFubmVsU2V0dGluZ3MSFwoLY2hhbm5lbF9udW0YASABKA1CAhgBEgsKA3BzaxgCIAEoDBIMCgRuYW1lGAMgASgJEgoKAmlkGAQgASgHEhYKDnVwbGlua19lbmFibGVkGAUgASgIEhgKEGRvd25saW5rX2VuYWJsZWQYBiABKAgSMwoPbW9kdWxlX3NldHRpbmdzGAcgASgLMhoubWVzaHRhc3RpYy5Nb2R1bGVTZXR0aW5ncyJFCg5Nb2R1bGVTZXR0aW5ncxIaChJwb3NpdGlvbl9wcmVjaXNpb24YASABKA0SFwoPaXNfY2xpZW50X211dGVkGAIgASgIIqEBCgdDaGFubmVsEg0KBWluZGV4GAEgASgFEi0KCHNldHRpbmdzGAIgASgLMhsubWVzaHRhc3RpYy5DaGFubmVsU2V0dGluZ3MSJgoEcm9sZRgDIAEoDjIYLm1lc2h0YXN0aWMuQ2hhbm5lbC5Sb2xlIjAKBFJvbGUSDAoIRElTQUJMRUQQABILCgdQUklNQVJZEAESDQoJU0VDT05EQVJZEAJCYgoTY29tLmdlZWtzdmlsbGUubWVzaEINQ2hhbm5lbFByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM"), Pr = /* @__PURE__ */ D(k, 0), Fr = /* @__PURE__ */ D(k, 1), Ir = /* @__PURE__ */ D(k, 2), Lr = /* @__PURE__ */ function(e) {
	return e[e.DISABLED = 0] = "DISABLED", e[e.PRIMARY = 1] = "PRIMARY", e[e.SECONDARY = 2] = "SECONDARY", e;
}({}), Rr = /* @__PURE__ */ w(k, 2, 0), zr = /* @__PURE__ */ E("Cg9kZXZpY2VfdWkucHJvdG8SCm1lc2h0YXN0aWMivgMKDkRldmljZVVJQ29uZmlnEg8KB3ZlcnNpb24YASABKA0SGQoRc2NyZWVuX2JyaWdodG5lc3MYAiABKA0SFgoOc2NyZWVuX3RpbWVvdXQYAyABKA0SEwoLc2NyZWVuX2xvY2sYBCABKAgSFQoNc2V0dGluZ3NfbG9jaxgFIAEoCBIQCghwaW5fY29kZRgGIAEoDRIgCgV0aGVtZRgHIAEoDjIRLm1lc2h0YXN0aWMuVGhlbWUSFQoNYWxlcnRfZW5hYmxlZBgIIAEoCBIWCg5iYW5uZXJfZW5hYmxlZBgJIAEoCBIUCgxyaW5nX3RvbmVfaWQYCiABKA0SJgoIbGFuZ3VhZ2UYCyABKA4yFC5tZXNodGFzdGljLkxhbmd1YWdlEisKC25vZGVfZmlsdGVyGAwgASgLMhYubWVzaHRhc3RpYy5Ob2RlRmlsdGVyEjEKDm5vZGVfaGlnaGxpZ2h0GA0gASgLMhkubWVzaHRhc3RpYy5Ob2RlSGlnaGxpZ2h0EhgKEGNhbGlicmF0aW9uX2RhdGEYDiABKAwSIQoIbWFwX2RhdGEYDyABKAsyDy5tZXNodGFzdGljLk1hcCKnAQoKTm9kZUZpbHRlchIWCg51bmtub3duX3N3aXRjaBgBIAEoCBIWCg5vZmZsaW5lX3N3aXRjaBgCIAEoCBIZChFwdWJsaWNfa2V5X3N3aXRjaBgDIAEoCBIRCglob3BzX2F3YXkYBCABKAUSFwoPcG9zaXRpb25fc3dpdGNoGAUgASgIEhEKCW5vZGVfbmFtZRgGIAEoCRIPCgdjaGFubmVsGAcgASgFIn4KDU5vZGVIaWdobGlnaHQSEwoLY2hhdF9zd2l0Y2gYASABKAgSFwoPcG9zaXRpb25fc3dpdGNoGAIgASgIEhgKEHRlbGVtZXRyeV9zd2l0Y2gYAyABKAgSEgoKaWFxX3N3aXRjaBgEIAEoCBIRCglub2RlX25hbWUYBSABKAkiPQoIR2VvUG9pbnQSDAoEem9vbRgBIAEoBRIQCghsYXRpdHVkZRgCIAEoBRIRCglsb25naXR1ZGUYAyABKAUiTAoDTWFwEiIKBGhvbWUYASABKAsyFC5tZXNodGFzdGljLkdlb1BvaW50Eg0KBXN0eWxlGAIgASgJEhIKCmZvbGxvd19ncHMYAyABKAgqJQoFVGhlbWUSCAoEREFSSxAAEgkKBUxJR0hUEAESBwoDUkVEEAIqqQIKCExhbmd1YWdlEgsKB0VOR0xJU0gQABIKCgZGUkVOQ0gQARIKCgZHRVJNQU4QAhILCgdJVEFMSUFOEAMSDgoKUE9SVFVHVUVTRRAEEgsKB1NQQU5JU0gQBRILCgdTV0VESVNIEAYSCwoHRklOTklTSBAHEgoKBlBPTElTSBAIEgsKB1RVUktJU0gQCRILCgdTRVJCSUFOEAoSCwoHUlVTU0lBThALEgkKBURVVENIEAwSCQoFR1JFRUsQDRINCglOT1JXRUdJQU4QDhINCglTTE9WRU5JQU4QDxINCglVS1JBSU5JQU4QEBINCglCVUxHQVJJQU4QERIWChJTSU1QTElGSUVEX0NISU5FU0UQHhIXChNUUkFESVRJT05BTF9DSElORVNFEB9CYwoTY29tLmdlZWtzdmlsbGUubWVzaEIORGV2aWNlVUlQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), Br = m({
	ConfigSchema: () => Vr,
	Config_BluetoothConfigSchema: () => Ci,
	Config_BluetoothConfig_PairingMode: () => wi,
	Config_BluetoothConfig_PairingModeSchema: () => Ti,
	Config_DeviceConfigSchema: () => Hr,
	Config_DeviceConfig_BuzzerMode: () => qr,
	Config_DeviceConfig_BuzzerModeSchema: () => Jr,
	Config_DeviceConfig_RebroadcastMode: () => Gr,
	Config_DeviceConfig_RebroadcastModeSchema: () => Kr,
	Config_DeviceConfig_Role: () => Ur,
	Config_DeviceConfig_RoleSchema: () => Wr,
	Config_DisplayConfigSchema: () => si,
	Config_DisplayConfig_CompassOrientation: () => gi,
	Config_DisplayConfig_CompassOrientationSchema: () => _i,
	Config_DisplayConfig_DisplayMode: () => mi,
	Config_DisplayConfig_DisplayModeSchema: () => hi,
	Config_DisplayConfig_DisplayUnits: () => ui,
	Config_DisplayConfig_DisplayUnitsSchema: () => di,
	Config_DisplayConfig_GpsCoordinateFormat: () => ci,
	Config_DisplayConfig_GpsCoordinateFormatSchema: () => li,
	Config_DisplayConfig_OledType: () => fi,
	Config_DisplayConfig_OledTypeSchema: () => pi,
	Config_LoRaConfigSchema: () => vi,
	Config_LoRaConfig_ModemPreset: () => xi,
	Config_LoRaConfig_ModemPresetSchema: () => Si,
	Config_LoRaConfig_RegionCode: () => yi,
	Config_LoRaConfig_RegionCodeSchema: () => bi,
	Config_NetworkConfigSchema: () => ti,
	Config_NetworkConfig_AddressMode: () => ri,
	Config_NetworkConfig_AddressModeSchema: () => ii,
	Config_NetworkConfig_IpV4ConfigSchema: () => ni,
	Config_NetworkConfig_ProtocolFlags: () => ai,
	Config_NetworkConfig_ProtocolFlagsSchema: () => oi,
	Config_PositionConfigSchema: () => Yr,
	Config_PositionConfig_GpsMode: () => Qr,
	Config_PositionConfig_GpsModeSchema: () => $r,
	Config_PositionConfig_PositionFlags: () => Xr,
	Config_PositionConfig_PositionFlagsSchema: () => Zr,
	Config_PowerConfigSchema: () => ei,
	Config_SecurityConfigSchema: () => Ei,
	Config_SessionkeyConfigSchema: () => Di,
	file_config: () => A
}), A = /* @__PURE__ */ E("Cgxjb25maWcucHJvdG8SCm1lc2h0YXN0aWMipigKBkNvbmZpZxIxCgZkZXZpY2UYASABKAsyHy5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWdIABI1Cghwb3NpdGlvbhgCIAEoCzIhLm1lc2h0YXN0aWMuQ29uZmlnLlBvc2l0aW9uQ29uZmlnSAASLwoFcG93ZXIYAyABKAsyHi5tZXNodGFzdGljLkNvbmZpZy5Qb3dlckNvbmZpZ0gAEjMKB25ldHdvcmsYBCABKAsyIC5tZXNodGFzdGljLkNvbmZpZy5OZXR3b3JrQ29uZmlnSAASMwoHZGlzcGxheRgFIAEoCzIgLm1lc2h0YXN0aWMuQ29uZmlnLkRpc3BsYXlDb25maWdIABItCgRsb3JhGAYgASgLMh0ubWVzaHRhc3RpYy5Db25maWcuTG9SYUNvbmZpZ0gAEjcKCWJsdWV0b290aBgHIAEoCzIiLm1lc2h0YXN0aWMuQ29uZmlnLkJsdWV0b290aENvbmZpZ0gAEjUKCHNlY3VyaXR5GAggASgLMiEubWVzaHRhc3RpYy5Db25maWcuU2VjdXJpdHlDb25maWdIABI5CgpzZXNzaW9ua2V5GAkgASgLMiMubWVzaHRhc3RpYy5Db25maWcuU2Vzc2lvbmtleUNvbmZpZ0gAEi8KCWRldmljZV91aRgKIAEoCzIaLm1lc2h0YXN0aWMuRGV2aWNlVUlDb25maWdIABrMBgoMRGV2aWNlQ29uZmlnEjIKBHJvbGUYASABKA4yJC5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWcuUm9sZRIaCg5zZXJpYWxfZW5hYmxlZBgCIAEoCEICGAESEwoLYnV0dG9uX2dwaW8YBCABKA0SEwoLYnV6emVyX2dwaW8YBSABKA0SSQoQcmVicm9hZGNhc3RfbW9kZRgGIAEoDjIvLm1lc2h0YXN0aWMuQ29uZmlnLkRldmljZUNvbmZpZy5SZWJyb2FkY2FzdE1vZGUSIAoYbm9kZV9pbmZvX2Jyb2FkY2FzdF9zZWNzGAcgASgNEiIKGmRvdWJsZV90YXBfYXNfYnV0dG9uX3ByZXNzGAggASgIEhYKCmlzX21hbmFnZWQYCSABKAhCAhgBEhwKFGRpc2FibGVfdHJpcGxlX2NsaWNrGAogASgIEg0KBXR6ZGVmGAsgASgJEh4KFmxlZF9oZWFydGJlYXRfZGlzYWJsZWQYDCABKAgSPwoLYnV6emVyX21vZGUYDSABKA4yKi5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWcuQnV6emVyTW9kZSK/AQoEUm9sZRIKCgZDTElFTlQQABIPCgtDTElFTlRfTVVURRABEgoKBlJPVVRFUhACEhUKDVJPVVRFUl9DTElFTlQQAxoCCAESDAoIUkVQRUFURVIQBBILCgdUUkFDS0VSEAUSCgoGU0VOU09SEAYSBwoDVEFLEAcSEQoNQ0xJRU5UX0hJRERFThAIEhIKDkxPU1RfQU5EX0ZPVU5EEAkSDwoLVEFLX1RSQUNLRVIQChIPCgtST1VURVJfTEFURRALInMKD1JlYnJvYWRjYXN0TW9kZRIHCgNBTEwQABIVChFBTExfU0tJUF9ERUNPRElORxABEg4KCkxPQ0FMX09OTFkQAhIOCgpLTk9XTl9PTkxZEAMSCAoETk9ORRAEEhYKEkNPUkVfUE9SVE5VTVNfT05MWRAFIlQKCkJ1enplck1vZGUSDwoLQUxMX0VOQUJMRUQQABIMCghESVNBQkxFRBABEhYKEk5PVElGSUNBVElPTlNfT05MWRACEg8KC1NZU1RFTV9PTkxZEAMakQUKDlBvc2l0aW9uQ29uZmlnEh8KF3Bvc2l0aW9uX2Jyb2FkY2FzdF9zZWNzGAEgASgNEigKIHBvc2l0aW9uX2Jyb2FkY2FzdF9zbWFydF9lbmFibGVkGAIgASgIEhYKDmZpeGVkX3Bvc2l0aW9uGAMgASgIEhcKC2dwc19lbmFibGVkGAQgASgIQgIYARIbChNncHNfdXBkYXRlX2ludGVydmFsGAUgASgNEhwKEGdwc19hdHRlbXB0X3RpbWUYBiABKA1CAhgBEhYKDnBvc2l0aW9uX2ZsYWdzGAcgASgNEg8KB3J4X2dwaW8YCCABKA0SDwoHdHhfZ3BpbxgJIAEoDRIoCiBicm9hZGNhc3Rfc21hcnRfbWluaW11bV9kaXN0YW5jZRgKIAEoDRItCiVicm9hZGNhc3Rfc21hcnRfbWluaW11bV9pbnRlcnZhbF9zZWNzGAsgASgNEhMKC2dwc19lbl9ncGlvGAwgASgNEjsKCGdwc19tb2RlGA0gASgOMikubWVzaHRhc3RpYy5Db25maWcuUG9zaXRpb25Db25maWcuR3BzTW9kZSKrAQoNUG9zaXRpb25GbGFncxIJCgVVTlNFVBAAEgwKCEFMVElUVURFEAESEAoMQUxUSVRVREVfTVNMEAISFgoSR0VPSURBTF9TRVBBUkFUSU9OEAQSBwoDRE9QEAgSCQoFSFZET1AQEBINCglTQVRJTlZJRVcQIBIKCgZTRVFfTk8QQBIOCglUSU1FU1RBTVAQgAESDAoHSEVBRElORxCAAhIKCgVTUEVFRBCABCI1CgdHcHNNb2RlEgwKCERJU0FCTEVEEAASCwoHRU5BQkxFRBABEg8KC05PVF9QUkVTRU5UEAIahAIKC1Bvd2VyQ29uZmlnEhcKD2lzX3Bvd2VyX3NhdmluZxgBIAEoCBImCh5vbl9iYXR0ZXJ5X3NodXRkb3duX2FmdGVyX3NlY3MYAiABKA0SHwoXYWRjX211bHRpcGxpZXJfb3ZlcnJpZGUYAyABKAISGwoTd2FpdF9ibHVldG9vdGhfc2VjcxgEIAEoDRIQCghzZHNfc2VjcxgGIAEoDRIPCgdsc19zZWNzGAcgASgNEhUKDW1pbl93YWtlX3NlY3MYCCABKA0SIgoaZGV2aWNlX2JhdHRlcnlfaW5hX2FkZHJlc3MYCSABKA0SGAoQcG93ZXJtb25fZW5hYmxlcxggIAEoBBrlAwoNTmV0d29ya0NvbmZpZxIUCgx3aWZpX2VuYWJsZWQYASABKAgSEQoJd2lmaV9zc2lkGAMgASgJEhAKCHdpZmlfcHNrGAQgASgJEhIKCm50cF9zZXJ2ZXIYBSABKAkSEwoLZXRoX2VuYWJsZWQYBiABKAgSQgoMYWRkcmVzc19tb2RlGAcgASgOMiwubWVzaHRhc3RpYy5Db25maWcuTmV0d29ya0NvbmZpZy5BZGRyZXNzTW9kZRJACgtpcHY0X2NvbmZpZxgIIAEoCzIrLm1lc2h0YXN0aWMuQ29uZmlnLk5ldHdvcmtDb25maWcuSXBWNENvbmZpZxIWCg5yc3lzbG9nX3NlcnZlchgJIAEoCRIZChFlbmFibGVkX3Byb3RvY29scxgKIAEoDRIUCgxpcHY2X2VuYWJsZWQYCyABKAgaRgoKSXBWNENvbmZpZxIKCgJpcBgBIAEoBxIPCgdnYXRld2F5GAIgASgHEg4KBnN1Ym5ldBgDIAEoBxILCgNkbnMYBCABKAciIwoLQWRkcmVzc01vZGUSCAoEREhDUBAAEgoKBlNUQVRJQxABIjQKDVByb3RvY29sRmxhZ3MSEAoMTk9fQlJPQURDQVNUEAASEQoNVURQX0JST0FEQ0FTVBABGvwHCg1EaXNwbGF5Q29uZmlnEhYKDnNjcmVlbl9vbl9zZWNzGAEgASgNEkgKCmdwc19mb3JtYXQYAiABKA4yNC5tZXNodGFzdGljLkNvbmZpZy5EaXNwbGF5Q29uZmlnLkdwc0Nvb3JkaW5hdGVGb3JtYXQSIQoZYXV0b19zY3JlZW5fY2Fyb3VzZWxfc2VjcxgDIAEoDRIZChFjb21wYXNzX25vcnRoX3RvcBgEIAEoCBITCgtmbGlwX3NjcmVlbhgFIAEoCBI8CgV1bml0cxgGIAEoDjItLm1lc2h0YXN0aWMuQ29uZmlnLkRpc3BsYXlDb25maWcuRGlzcGxheVVuaXRzEjcKBG9sZWQYByABKA4yKS5tZXNodGFzdGljLkNvbmZpZy5EaXNwbGF5Q29uZmlnLk9sZWRUeXBlEkEKC2Rpc3BsYXltb2RlGAggASgOMiwubWVzaHRhc3RpYy5Db25maWcuRGlzcGxheUNvbmZpZy5EaXNwbGF5TW9kZRIUCgxoZWFkaW5nX2JvbGQYCSABKAgSHQoVd2FrZV9vbl90YXBfb3JfbW90aW9uGAogASgIElAKE2NvbXBhc3Nfb3JpZW50YXRpb24YCyABKA4yMy5tZXNodGFzdGljLkNvbmZpZy5EaXNwbGF5Q29uZmlnLkNvbXBhc3NPcmllbnRhdGlvbhIVCg11c2VfMTJoX2Nsb2NrGAwgASgIIk0KE0dwc0Nvb3JkaW5hdGVGb3JtYXQSBwoDREVDEAASBwoDRE1TEAESBwoDVVRNEAISCAoETUdSUxADEgcKA09MQxAEEggKBE9TR1IQBSIoCgxEaXNwbGF5VW5pdHMSCgoGTUVUUklDEAASDAoISU1QRVJJQUwQASJlCghPbGVkVHlwZRINCglPTEVEX0FVVE8QABIQCgxPTEVEX1NTRDEzMDYQARIPCgtPTEVEX1NIMTEwNhACEg8KC09MRURfU0gxMTA3EAMSFgoST0xFRF9TSDExMDdfMTI4XzY0EAQiQQoLRGlzcGxheU1vZGUSCwoHREVGQVVMVBAAEgwKCFRXT0NPTE9SEAESDAoISU5WRVJURUQQAhIJCgVDT0xPUhADIroBChJDb21wYXNzT3JpZW50YXRpb24SDQoJREVHUkVFU18wEAASDgoKREVHUkVFU185MBABEg8KC0RFR1JFRVNfMTgwEAISDwoLREVHUkVFU18yNzAQAxIWChJERUdSRUVTXzBfSU5WRVJURUQQBBIXChNERUdSRUVTXzkwX0lOVkVSVEVEEAUSGAoUREVHUkVFU18xODBfSU5WRVJURUQQBhIYChRERUdSRUVTXzI3MF9JTlZFUlRFRBAHGqoHCgpMb1JhQ29uZmlnEhIKCnVzZV9wcmVzZXQYASABKAgSPwoMbW9kZW1fcHJlc2V0GAIgASgOMikubWVzaHRhc3RpYy5Db25maWcuTG9SYUNvbmZpZy5Nb2RlbVByZXNldBIRCgliYW5kd2lkdGgYAyABKA0SFQoNc3ByZWFkX2ZhY3RvchgEIAEoDRITCgtjb2RpbmdfcmF0ZRgFIAEoDRIYChBmcmVxdWVuY3lfb2Zmc2V0GAYgASgCEjgKBnJlZ2lvbhgHIAEoDjIoLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWcuUmVnaW9uQ29kZRIRCglob3BfbGltaXQYCCABKA0SEgoKdHhfZW5hYmxlZBgJIAEoCBIQCgh0eF9wb3dlchgKIAEoBRITCgtjaGFubmVsX251bRgLIAEoDRIbChNvdmVycmlkZV9kdXR5X2N5Y2xlGAwgASgIEh4KFnN4MTI2eF9yeF9ib29zdGVkX2dhaW4YDSABKAgSGgoSb3ZlcnJpZGVfZnJlcXVlbmN5GA4gASgCEhcKD3BhX2Zhbl9kaXNhYmxlZBgPIAEoCBIXCg9pZ25vcmVfaW5jb21pbmcYZyADKA0SEwoLaWdub3JlX21xdHQYaCABKAgSGQoRY29uZmlnX29rX3RvX21xdHQYaSABKAgi/gEKClJlZ2lvbkNvZGUSCQoFVU5TRVQQABIGCgJVUxABEgoKBkVVXzQzMxACEgoKBkVVXzg2OBADEgYKAkNOEAQSBgoCSlAQBRIHCgNBTloQBhIGCgJLUhAHEgYKAlRXEAgSBgoCUlUQCRIGCgJJThAKEgoKBk5aXzg2NRALEgYKAlRIEAwSCwoHTE9SQV8yNBANEgoKBlVBXzQzMxAOEgoKBlVBXzg2OBAPEgoKBk1ZXzQzMxAQEgoKBk1ZXzkxORAREgoKBlNHXzkyMxASEgoKBlBIXzQzMxATEgoKBlBIXzg2OBAUEgoKBlBIXzkxNRAVEgsKB0FOWl80MzMQFiKpAQoLTW9kZW1QcmVzZXQSDQoJTE9OR19GQVNUEAASDQoJTE9OR19TTE9XEAESFgoOVkVSWV9MT05HX1NMT1cQAhoCCAESDwoLTUVESVVNX1NMT1cQAxIPCgtNRURJVU1fRkFTVBAEEg4KClNIT1JUX1NMT1cQBRIOCgpTSE9SVF9GQVNUEAYSEQoNTE9OR19NT0RFUkFURRAHEg8KC1NIT1JUX1RVUkJPEAgarQEKD0JsdWV0b290aENvbmZpZxIPCgdlbmFibGVkGAEgASgIEjwKBG1vZGUYAiABKA4yLi5tZXNodGFzdGljLkNvbmZpZy5CbHVldG9vdGhDb25maWcuUGFpcmluZ01vZGUSEQoJZml4ZWRfcGluGAMgASgNIjgKC1BhaXJpbmdNb2RlEg4KClJBTkRPTV9QSU4QABINCglGSVhFRF9QSU4QARIKCgZOT19QSU4QAhq2AQoOU2VjdXJpdHlDb25maWcSEgoKcHVibGljX2tleRgBIAEoDBITCgtwcml2YXRlX2tleRgCIAEoDBIRCglhZG1pbl9rZXkYAyADKAwSEgoKaXNfbWFuYWdlZBgEIAEoCBIWCg5zZXJpYWxfZW5hYmxlZBgFIAEoCBIdChVkZWJ1Z19sb2dfYXBpX2VuYWJsZWQYBiABKAgSHQoVYWRtaW5fY2hhbm5lbF9lbmFibGVkGAggASgIGhIKEFNlc3Npb25rZXlDb25maWdCEQoPcGF5bG9hZF92YXJpYW50QmEKE2NvbS5nZWVrc3ZpbGxlLm1lc2hCDENvbmZpZ1Byb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [zr]), Vr = /* @__PURE__ */ D(A, 0), Hr = /* @__PURE__ */ D(A, 0, 0), Ur = /* @__PURE__ */ function(e) {
	return e[e.CLIENT = 0] = "CLIENT", e[e.CLIENT_MUTE = 1] = "CLIENT_MUTE", e[e.ROUTER = 2] = "ROUTER", e[e.ROUTER_CLIENT = 3] = "ROUTER_CLIENT", e[e.REPEATER = 4] = "REPEATER", e[e.TRACKER = 5] = "TRACKER", e[e.SENSOR = 6] = "SENSOR", e[e.TAK = 7] = "TAK", e[e.CLIENT_HIDDEN = 8] = "CLIENT_HIDDEN", e[e.LOST_AND_FOUND = 9] = "LOST_AND_FOUND", e[e.TAK_TRACKER = 10] = "TAK_TRACKER", e[e.ROUTER_LATE = 11] = "ROUTER_LATE", e;
}({}), Wr = /* @__PURE__ */ w(A, 0, 0, 0), Gr = /* @__PURE__ */ function(e) {
	return e[e.ALL = 0] = "ALL", e[e.ALL_SKIP_DECODING = 1] = "ALL_SKIP_DECODING", e[e.LOCAL_ONLY = 2] = "LOCAL_ONLY", e[e.KNOWN_ONLY = 3] = "KNOWN_ONLY", e[e.NONE = 4] = "NONE", e[e.CORE_PORTNUMS_ONLY = 5] = "CORE_PORTNUMS_ONLY", e;
}({}), Kr = /* @__PURE__ */ w(A, 0, 0, 1), qr = /* @__PURE__ */ function(e) {
	return e[e.ALL_ENABLED = 0] = "ALL_ENABLED", e[e.DISABLED = 1] = "DISABLED", e[e.NOTIFICATIONS_ONLY = 2] = "NOTIFICATIONS_ONLY", e[e.SYSTEM_ONLY = 3] = "SYSTEM_ONLY", e;
}({}), Jr = /* @__PURE__ */ w(A, 0, 0, 2), Yr = /* @__PURE__ */ D(A, 0, 1), Xr = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.ALTITUDE = 1] = "ALTITUDE", e[e.ALTITUDE_MSL = 2] = "ALTITUDE_MSL", e[e.GEOIDAL_SEPARATION = 4] = "GEOIDAL_SEPARATION", e[e.DOP = 8] = "DOP", e[e.HVDOP = 16] = "HVDOP", e[e.SATINVIEW = 32] = "SATINVIEW", e[e.SEQ_NO = 64] = "SEQ_NO", e[e.TIMESTAMP = 128] = "TIMESTAMP", e[e.HEADING = 256] = "HEADING", e[e.SPEED = 512] = "SPEED", e;
}({}), Zr = /* @__PURE__ */ w(A, 0, 1, 0), Qr = /* @__PURE__ */ function(e) {
	return e[e.DISABLED = 0] = "DISABLED", e[e.ENABLED = 1] = "ENABLED", e[e.NOT_PRESENT = 2] = "NOT_PRESENT", e;
}({}), $r = /* @__PURE__ */ w(A, 0, 1, 1), ei = /* @__PURE__ */ D(A, 0, 2), ti = /* @__PURE__ */ D(A, 0, 3), ni = /* @__PURE__ */ D(A, 0, 3, 0), ri = /* @__PURE__ */ function(e) {
	return e[e.DHCP = 0] = "DHCP", e[e.STATIC = 1] = "STATIC", e;
}({}), ii = /* @__PURE__ */ w(A, 0, 3, 0), ai = /* @__PURE__ */ function(e) {
	return e[e.NO_BROADCAST = 0] = "NO_BROADCAST", e[e.UDP_BROADCAST = 1] = "UDP_BROADCAST", e;
}({}), oi = /* @__PURE__ */ w(A, 0, 3, 1), si = /* @__PURE__ */ D(A, 0, 4), ci = /* @__PURE__ */ function(e) {
	return e[e.DEC = 0] = "DEC", e[e.DMS = 1] = "DMS", e[e.UTM = 2] = "UTM", e[e.MGRS = 3] = "MGRS", e[e.OLC = 4] = "OLC", e[e.OSGR = 5] = "OSGR", e;
}({}), li = /* @__PURE__ */ w(A, 0, 4, 0), ui = /* @__PURE__ */ function(e) {
	return e[e.METRIC = 0] = "METRIC", e[e.IMPERIAL = 1] = "IMPERIAL", e;
}({}), di = /* @__PURE__ */ w(A, 0, 4, 1), fi = /* @__PURE__ */ function(e) {
	return e[e.OLED_AUTO = 0] = "OLED_AUTO", e[e.OLED_SSD1306 = 1] = "OLED_SSD1306", e[e.OLED_SH1106 = 2] = "OLED_SH1106", e[e.OLED_SH1107 = 3] = "OLED_SH1107", e[e.OLED_SH1107_128_64 = 4] = "OLED_SH1107_128_64", e;
}({}), pi = /* @__PURE__ */ w(A, 0, 4, 2), mi = /* @__PURE__ */ function(e) {
	return e[e.DEFAULT = 0] = "DEFAULT", e[e.TWOCOLOR = 1] = "TWOCOLOR", e[e.INVERTED = 2] = "INVERTED", e[e.COLOR = 3] = "COLOR", e;
}({}), hi = /* @__PURE__ */ w(A, 0, 4, 3), gi = /* @__PURE__ */ function(e) {
	return e[e.DEGREES_0 = 0] = "DEGREES_0", e[e.DEGREES_90 = 1] = "DEGREES_90", e[e.DEGREES_180 = 2] = "DEGREES_180", e[e.DEGREES_270 = 3] = "DEGREES_270", e[e.DEGREES_0_INVERTED = 4] = "DEGREES_0_INVERTED", e[e.DEGREES_90_INVERTED = 5] = "DEGREES_90_INVERTED", e[e.DEGREES_180_INVERTED = 6] = "DEGREES_180_INVERTED", e[e.DEGREES_270_INVERTED = 7] = "DEGREES_270_INVERTED", e;
}({}), _i = /* @__PURE__ */ w(A, 0, 4, 4), vi = /* @__PURE__ */ D(A, 0, 5), yi = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.US = 1] = "US", e[e.EU_433 = 2] = "EU_433", e[e.EU_868 = 3] = "EU_868", e[e.CN = 4] = "CN", e[e.JP = 5] = "JP", e[e.ANZ = 6] = "ANZ", e[e.KR = 7] = "KR", e[e.TW = 8] = "TW", e[e.RU = 9] = "RU", e[e.IN = 10] = "IN", e[e.NZ_865 = 11] = "NZ_865", e[e.TH = 12] = "TH", e[e.LORA_24 = 13] = "LORA_24", e[e.UA_433 = 14] = "UA_433", e[e.UA_868 = 15] = "UA_868", e[e.MY_433 = 16] = "MY_433", e[e.MY_919 = 17] = "MY_919", e[e.SG_923 = 18] = "SG_923", e[e.PH_433 = 19] = "PH_433", e[e.PH_868 = 20] = "PH_868", e[e.PH_915 = 21] = "PH_915", e[e.ANZ_433 = 22] = "ANZ_433", e;
}({}), bi = /* @__PURE__ */ w(A, 0, 5, 0), xi = /* @__PURE__ */ function(e) {
	return e[e.LONG_FAST = 0] = "LONG_FAST", e[e.LONG_SLOW = 1] = "LONG_SLOW", e[e.VERY_LONG_SLOW = 2] = "VERY_LONG_SLOW", e[e.MEDIUM_SLOW = 3] = "MEDIUM_SLOW", e[e.MEDIUM_FAST = 4] = "MEDIUM_FAST", e[e.SHORT_SLOW = 5] = "SHORT_SLOW", e[e.SHORT_FAST = 6] = "SHORT_FAST", e[e.LONG_MODERATE = 7] = "LONG_MODERATE", e[e.SHORT_TURBO = 8] = "SHORT_TURBO", e;
}({}), Si = /* @__PURE__ */ w(A, 0, 5, 1), Ci = /* @__PURE__ */ D(A, 0, 6), wi = /* @__PURE__ */ function(e) {
	return e[e.RANDOM_PIN = 0] = "RANDOM_PIN", e[e.FIXED_PIN = 1] = "FIXED_PIN", e[e.NO_PIN = 2] = "NO_PIN", e;
}({}), Ti = /* @__PURE__ */ w(A, 0, 6, 0), Ei = /* @__PURE__ */ D(A, 0, 7), Di = /* @__PURE__ */ D(A, 0, 8), Oi = m({
	BluetoothConnectionStatusSchema: () => Ni,
	DeviceConnectionStatusSchema: () => ki,
	EthernetConnectionStatusSchema: () => ji,
	NetworkConnectionStatusSchema: () => Mi,
	SerialConnectionStatusSchema: () => Pi,
	WifiConnectionStatusSchema: () => Ai,
	file_connection_status: () => j
}), j = /* @__PURE__ */ E("Chdjb25uZWN0aW9uX3N0YXR1cy5wcm90bxIKbWVzaHRhc3RpYyKxAgoWRGV2aWNlQ29ubmVjdGlvblN0YXR1cxIzCgR3aWZpGAEgASgLMiAubWVzaHRhc3RpYy5XaWZpQ29ubmVjdGlvblN0YXR1c0gAiAEBEjsKCGV0aGVybmV0GAIgASgLMiQubWVzaHRhc3RpYy5FdGhlcm5ldENvbm5lY3Rpb25TdGF0dXNIAYgBARI9CglibHVldG9vdGgYAyABKAsyJS5tZXNodGFzdGljLkJsdWV0b290aENvbm5lY3Rpb25TdGF0dXNIAogBARI3CgZzZXJpYWwYBCABKAsyIi5tZXNodGFzdGljLlNlcmlhbENvbm5lY3Rpb25TdGF0dXNIA4gBAUIHCgVfd2lmaUILCglfZXRoZXJuZXRCDAoKX2JsdWV0b290aEIJCgdfc2VyaWFsImcKFFdpZmlDb25uZWN0aW9uU3RhdHVzEjMKBnN0YXR1cxgBIAEoCzIjLm1lc2h0YXN0aWMuTmV0d29ya0Nvbm5lY3Rpb25TdGF0dXMSDAoEc3NpZBgCIAEoCRIMCgRyc3NpGAMgASgFIk8KGEV0aGVybmV0Q29ubmVjdGlvblN0YXR1cxIzCgZzdGF0dXMYASABKAsyIy5tZXNodGFzdGljLk5ldHdvcmtDb25uZWN0aW9uU3RhdHVzInsKF05ldHdvcmtDb25uZWN0aW9uU3RhdHVzEhIKCmlwX2FkZHJlc3MYASABKAcSFAoMaXNfY29ubmVjdGVkGAIgASgIEhkKEWlzX21xdHRfY29ubmVjdGVkGAMgASgIEhsKE2lzX3N5c2xvZ19jb25uZWN0ZWQYBCABKAgiTAoZQmx1ZXRvb3RoQ29ubmVjdGlvblN0YXR1cxILCgNwaW4YASABKA0SDAoEcnNzaRgCIAEoBRIUCgxpc19jb25uZWN0ZWQYAyABKAgiPAoWU2VyaWFsQ29ubmVjdGlvblN0YXR1cxIMCgRiYXVkGAEgASgNEhQKDGlzX2Nvbm5lY3RlZBgCIAEoCEJlChNjb20uZ2Vla3N2aWxsZS5tZXNoQhBDb25uU3RhdHVzUHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), ki = /* @__PURE__ */ D(j, 0), Ai = /* @__PURE__ */ D(j, 1), ji = /* @__PURE__ */ D(j, 2), Mi = /* @__PURE__ */ D(j, 3), Ni = /* @__PURE__ */ D(j, 4), Pi = /* @__PURE__ */ D(j, 5), Fi = m({
	ModuleConfigSchema: () => Ii,
	ModuleConfig_AmbientLightingConfigSchema: () => oa,
	ModuleConfig_AudioConfigSchema: () => Wi,
	ModuleConfig_AudioConfig_Audio_Baud: () => Gi,
	ModuleConfig_AudioConfig_Audio_BaudSchema: () => Ki,
	ModuleConfig_CannedMessageConfigSchema: () => ra,
	ModuleConfig_CannedMessageConfig_InputEventChar: () => ia,
	ModuleConfig_CannedMessageConfig_InputEventCharSchema: () => aa,
	ModuleConfig_DetectionSensorConfigSchema: () => Vi,
	ModuleConfig_DetectionSensorConfig_TriggerType: () => Hi,
	ModuleConfig_DetectionSensorConfig_TriggerTypeSchema: () => Ui,
	ModuleConfig_ExternalNotificationConfigSchema: () => $i,
	ModuleConfig_MQTTConfigSchema: () => Li,
	ModuleConfig_MapReportSettingsSchema: () => Ri,
	ModuleConfig_NeighborInfoConfigSchema: () => Bi,
	ModuleConfig_PaxcounterConfigSchema: () => qi,
	ModuleConfig_RangeTestConfigSchema: () => ta,
	ModuleConfig_RemoteHardwareConfigSchema: () => zi,
	ModuleConfig_SerialConfigSchema: () => Ji,
	ModuleConfig_SerialConfig_Serial_Baud: () => Yi,
	ModuleConfig_SerialConfig_Serial_BaudSchema: () => Xi,
	ModuleConfig_SerialConfig_Serial_Mode: () => Zi,
	ModuleConfig_SerialConfig_Serial_ModeSchema: () => Qi,
	ModuleConfig_StoreForwardConfigSchema: () => ea,
	ModuleConfig_TelemetryConfigSchema: () => na,
	RemoteHardwarePinSchema: () => sa,
	RemoteHardwarePinType: () => ca,
	RemoteHardwarePinTypeSchema: () => la,
	file_module_config: () => M
}), M = /* @__PURE__ */ E("ChNtb2R1bGVfY29uZmlnLnByb3RvEgptZXNodGFzdGljIuMlCgxNb2R1bGVDb25maWcSMwoEbXF0dBgBIAEoCzIjLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk1RVFRDb25maWdIABI3CgZzZXJpYWwYAiABKAsyJS5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TZXJpYWxDb25maWdIABJUChVleHRlcm5hbF9ub3RpZmljYXRpb24YAyABKAsyMy5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5FeHRlcm5hbE5vdGlmaWNhdGlvbkNvbmZpZ0gAEkQKDXN0b3JlX2ZvcndhcmQYBCABKAsyKy5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TdG9yZUZvcndhcmRDb25maWdIABI+CgpyYW5nZV90ZXN0GAUgASgLMigubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuUmFuZ2VUZXN0Q29uZmlnSAASPQoJdGVsZW1ldHJ5GAYgASgLMigubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuVGVsZW1ldHJ5Q29uZmlnSAASRgoOY2FubmVkX21lc3NhZ2UYByABKAsyLC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5DYW5uZWRNZXNzYWdlQ29uZmlnSAASNQoFYXVkaW8YCCABKAsyJC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5BdWRpb0NvbmZpZ0gAEkgKD3JlbW90ZV9oYXJkd2FyZRgJIAEoCzItLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlJlbW90ZUhhcmR3YXJlQ29uZmlnSAASRAoNbmVpZ2hib3JfaW5mbxgKIAEoCzIrLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk5laWdoYm9ySW5mb0NvbmZpZ0gAEkoKEGFtYmllbnRfbGlnaHRpbmcYCyABKAsyLi5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5BbWJpZW50TGlnaHRpbmdDb25maWdIABJKChBkZXRlY3Rpb25fc2Vuc29yGAwgASgLMi4ubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuRGV0ZWN0aW9uU2Vuc29yQ29uZmlnSAASPwoKcGF4Y291bnRlchgNIAEoCzIpLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlBheGNvdW50ZXJDb25maWdIABqwAgoKTVFUVENvbmZpZxIPCgdlbmFibGVkGAEgASgIEg8KB2FkZHJlc3MYAiABKAkSEAoIdXNlcm5hbWUYAyABKAkSEAoIcGFzc3dvcmQYBCABKAkSGgoSZW5jcnlwdGlvbl9lbmFibGVkGAUgASgIEhQKDGpzb25fZW5hYmxlZBgGIAEoCBITCgt0bHNfZW5hYmxlZBgHIAEoCBIMCgRyb290GAggASgJEh8KF3Byb3h5X3RvX2NsaWVudF9lbmFibGVkGAkgASgIEh0KFW1hcF9yZXBvcnRpbmdfZW5hYmxlZBgKIAEoCBJHChNtYXBfcmVwb3J0X3NldHRpbmdzGAsgASgLMioubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuTWFwUmVwb3J0U2V0dGluZ3MabgoRTWFwUmVwb3J0U2V0dGluZ3MSHQoVcHVibGlzaF9pbnRlcnZhbF9zZWNzGAEgASgNEhoKEnBvc2l0aW9uX3ByZWNpc2lvbhgCIAEoDRIeChZzaG91bGRfcmVwb3J0X2xvY2F0aW9uGAMgASgIGoIBChRSZW1vdGVIYXJkd2FyZUNvbmZpZxIPCgdlbmFibGVkGAEgASgIEiIKGmFsbG93X3VuZGVmaW5lZF9waW5fYWNjZXNzGAIgASgIEjUKDmF2YWlsYWJsZV9waW5zGAMgAygLMh0ubWVzaHRhc3RpYy5SZW1vdGVIYXJkd2FyZVBpbhpaChJOZWlnaGJvckluZm9Db25maWcSDwoHZW5hYmxlZBgBIAEoCBIXCg91cGRhdGVfaW50ZXJ2YWwYAiABKA0SGgoSdHJhbnNtaXRfb3Zlcl9sb3JhGAMgASgIGpcDChVEZXRlY3Rpb25TZW5zb3JDb25maWcSDwoHZW5hYmxlZBgBIAEoCBIeChZtaW5pbXVtX2Jyb2FkY2FzdF9zZWNzGAIgASgNEhwKFHN0YXRlX2Jyb2FkY2FzdF9zZWNzGAMgASgNEhEKCXNlbmRfYmVsbBgEIAEoCBIMCgRuYW1lGAUgASgJEhMKC21vbml0b3JfcGluGAYgASgNEloKFmRldGVjdGlvbl90cmlnZ2VyX3R5cGUYByABKA4yOi5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5EZXRlY3Rpb25TZW5zb3JDb25maWcuVHJpZ2dlclR5cGUSEgoKdXNlX3B1bGx1cBgIIAEoCCKIAQoLVHJpZ2dlclR5cGUSDQoJTE9HSUNfTE9XEAASDgoKTE9HSUNfSElHSBABEhAKDEZBTExJTkdfRURHRRACEg8KC1JJU0lOR19FREdFEAMSGgoWRUlUSEVSX0VER0VfQUNUSVZFX0xPVxAEEhsKF0VJVEhFUl9FREdFX0FDVElWRV9ISUdIEAUa5AIKC0F1ZGlvQ29uZmlnEhYKDmNvZGVjMl9lbmFibGVkGAEgASgIEg8KB3B0dF9waW4YAiABKA0SQAoHYml0cmF0ZRgDIAEoDjIvLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkF1ZGlvQ29uZmlnLkF1ZGlvX0JhdWQSDgoGaTJzX3dzGAQgASgNEg4KBmkyc19zZBgFIAEoDRIPCgdpMnNfZGluGAYgASgNEg8KB2kyc19zY2sYByABKA0ipwEKCkF1ZGlvX0JhdWQSEgoOQ09ERUMyX0RFRkFVTFQQABIPCgtDT0RFQzJfMzIwMBABEg8KC0NPREVDMl8yNDAwEAISDwoLQ09ERUMyXzE2MDAQAxIPCgtDT0RFQzJfMTQwMBAEEg8KC0NPREVDMl8xMzAwEAUSDwoLQ09ERUMyXzEyMDAQBhIOCgpDT0RFQzJfNzAwEAcSDwoLQ09ERUMyXzcwMEIQCBp2ChBQYXhjb3VudGVyQ29uZmlnEg8KB2VuYWJsZWQYASABKAgSIgoacGF4Y291bnRlcl91cGRhdGVfaW50ZXJ2YWwYAiABKA0SFgoOd2lmaV90aHJlc2hvbGQYAyABKAUSFQoNYmxlX3RocmVzaG9sZBgEIAEoBRr9BAoMU2VyaWFsQ29uZmlnEg8KB2VuYWJsZWQYASABKAgSDAoEZWNobxgCIAEoCBILCgNyeGQYAyABKA0SCwoDdHhkGAQgASgNEj8KBGJhdWQYBSABKA4yMS5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TZXJpYWxDb25maWcuU2VyaWFsX0JhdWQSDwoHdGltZW91dBgGIAEoDRI/CgRtb2RlGAcgASgOMjEubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuU2VyaWFsQ29uZmlnLlNlcmlhbF9Nb2RlEiQKHG92ZXJyaWRlX2NvbnNvbGVfc2VyaWFsX3BvcnQYCCABKAgiigIKC1NlcmlhbF9CYXVkEhAKDEJBVURfREVGQVVMVBAAEgwKCEJBVURfMTEwEAESDAoIQkFVRF8zMDAQAhIMCghCQVVEXzYwMBADEg0KCUJBVURfMTIwMBAEEg0KCUJBVURfMjQwMBAFEg0KCUJBVURfNDgwMBAGEg0KCUJBVURfOTYwMBAHEg4KCkJBVURfMTkyMDAQCBIOCgpCQVVEXzM4NDAwEAkSDgoKQkFVRF81NzYwMBAKEg8KC0JBVURfMTE1MjAwEAsSDwoLQkFVRF8yMzA0MDAQDBIPCgtCQVVEXzQ2MDgwMBANEg8KC0JBVURfNTc2MDAwEA4SDwoLQkFVRF85MjE2MDAQDyJuCgtTZXJpYWxfTW9kZRILCgdERUZBVUxUEAASCgoGU0lNUExFEAESCQoFUFJPVE8QAhILCgdURVhUTVNHEAMSCAoETk1FQRAEEgsKB0NBTFRPUE8QBRIICgRXUzg1EAYSDQoJVkVfRElSRUNUEAca6QIKGkV4dGVybmFsTm90aWZpY2F0aW9uQ29uZmlnEg8KB2VuYWJsZWQYASABKAgSEQoJb3V0cHV0X21zGAIgASgNEg4KBm91dHB1dBgDIAEoDRIUCgxvdXRwdXRfdmlicmEYCCABKA0SFQoNb3V0cHV0X2J1enplchgJIAEoDRIOCgZhY3RpdmUYBCABKAgSFQoNYWxlcnRfbWVzc2FnZRgFIAEoCBIbChNhbGVydF9tZXNzYWdlX3ZpYnJhGAogASgIEhwKFGFsZXJ0X21lc3NhZ2VfYnV6emVyGAsgASgIEhIKCmFsZXJ0X2JlbGwYBiABKAgSGAoQYWxlcnRfYmVsbF92aWJyYRgMIAEoCBIZChFhbGVydF9iZWxsX2J1enplchgNIAEoCBIPCgd1c2VfcHdtGAcgASgIEhMKC25hZ190aW1lb3V0GA4gASgNEhkKEXVzZV9pMnNfYXNfYnV6emVyGA8gASgIGpcBChJTdG9yZUZvcndhcmRDb25maWcSDwoHZW5hYmxlZBgBIAEoCBIRCgloZWFydGJlYXQYAiABKAgSDwoHcmVjb3JkcxgDIAEoDRIaChJoaXN0b3J5X3JldHVybl9tYXgYBCABKA0SHQoVaGlzdG9yeV9yZXR1cm5fd2luZG93GAUgASgNEhEKCWlzX3NlcnZlchgGIAEoCBpACg9SYW5nZVRlc3RDb25maWcSDwoHZW5hYmxlZBgBIAEoCBIOCgZzZW5kZXIYAiABKA0SDAoEc2F2ZRgDIAEoCBrJAwoPVGVsZW1ldHJ5Q29uZmlnEh4KFmRldmljZV91cGRhdGVfaW50ZXJ2YWwYASABKA0SIwobZW52aXJvbm1lbnRfdXBkYXRlX2ludGVydmFsGAIgASgNEicKH2Vudmlyb25tZW50X21lYXN1cmVtZW50X2VuYWJsZWQYAyABKAgSIgoaZW52aXJvbm1lbnRfc2NyZWVuX2VuYWJsZWQYBCABKAgSJgoeZW52aXJvbm1lbnRfZGlzcGxheV9mYWhyZW5oZWl0GAUgASgIEhsKE2Fpcl9xdWFsaXR5X2VuYWJsZWQYBiABKAgSHAoUYWlyX3F1YWxpdHlfaW50ZXJ2YWwYByABKA0SIQoZcG93ZXJfbWVhc3VyZW1lbnRfZW5hYmxlZBgIIAEoCBIdChVwb3dlcl91cGRhdGVfaW50ZXJ2YWwYCSABKA0SHAoUcG93ZXJfc2NyZWVuX2VuYWJsZWQYCiABKAgSIgoaaGVhbHRoX21lYXN1cmVtZW50X2VuYWJsZWQYCyABKAgSHgoWaGVhbHRoX3VwZGF0ZV9pbnRlcnZhbBgMIAEoDRIdChVoZWFsdGhfc2NyZWVuX2VuYWJsZWQYDSABKAga1gQKE0Nhbm5lZE1lc3NhZ2VDb25maWcSFwoPcm90YXJ5MV9lbmFibGVkGAEgASgIEhkKEWlucHV0YnJva2VyX3Bpbl9hGAIgASgNEhkKEWlucHV0YnJva2VyX3Bpbl9iGAMgASgNEh0KFWlucHV0YnJva2VyX3Bpbl9wcmVzcxgEIAEoDRJZChRpbnB1dGJyb2tlcl9ldmVudF9jdxgFIAEoDjI7Lm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkNhbm5lZE1lc3NhZ2VDb25maWcuSW5wdXRFdmVudENoYXISWgoVaW5wdXRicm9rZXJfZXZlbnRfY2N3GAYgASgOMjsubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuQ2FubmVkTWVzc2FnZUNvbmZpZy5JbnB1dEV2ZW50Q2hhchJcChdpbnB1dGJyb2tlcl9ldmVudF9wcmVzcxgHIAEoDjI7Lm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkNhbm5lZE1lc3NhZ2VDb25maWcuSW5wdXRFdmVudENoYXISFwoPdXBkb3duMV9lbmFibGVkGAggASgIEg8KB2VuYWJsZWQYCSABKAgSGgoSYWxsb3dfaW5wdXRfc291cmNlGAogASgJEhEKCXNlbmRfYmVsbBgLIAEoCCJjCg5JbnB1dEV2ZW50Q2hhchIICgROT05FEAASBgoCVVAQERIICgRET1dOEBISCAoETEVGVBATEgkKBVJJR0hUEBQSCgoGU0VMRUNUEAoSCAoEQkFDSxAbEgoKBkNBTkNFTBAYGmUKFUFtYmllbnRMaWdodGluZ0NvbmZpZxIRCglsZWRfc3RhdGUYASABKAgSDwoHY3VycmVudBgCIAEoDRILCgNyZWQYAyABKA0SDQoFZ3JlZW4YBCABKA0SDAoEYmx1ZRgFIAEoDUIRCg9wYXlsb2FkX3ZhcmlhbnQiZAoRUmVtb3RlSGFyZHdhcmVQaW4SEAoIZ3Bpb19waW4YASABKA0SDAoEbmFtZRgCIAEoCRIvCgR0eXBlGAMgASgOMiEubWVzaHRhc3RpYy5SZW1vdGVIYXJkd2FyZVBpblR5cGUqSQoVUmVtb3RlSGFyZHdhcmVQaW5UeXBlEgsKB1VOS05PV04QABIQCgxESUdJVEFMX1JFQUQQARIRCg1ESUdJVEFMX1dSSVRFEAJCZwoTY29tLmdlZWtzdmlsbGUubWVzaEISTW9kdWxlQ29uZmlnUHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), Ii = /* @__PURE__ */ D(M, 0), Li = /* @__PURE__ */ D(M, 0, 0), Ri = /* @__PURE__ */ D(M, 0, 1), zi = /* @__PURE__ */ D(M, 0, 2), Bi = /* @__PURE__ */ D(M, 0, 3), Vi = /* @__PURE__ */ D(M, 0, 4), Hi = /* @__PURE__ */ function(e) {
	return e[e.LOGIC_LOW = 0] = "LOGIC_LOW", e[e.LOGIC_HIGH = 1] = "LOGIC_HIGH", e[e.FALLING_EDGE = 2] = "FALLING_EDGE", e[e.RISING_EDGE = 3] = "RISING_EDGE", e[e.EITHER_EDGE_ACTIVE_LOW = 4] = "EITHER_EDGE_ACTIVE_LOW", e[e.EITHER_EDGE_ACTIVE_HIGH = 5] = "EITHER_EDGE_ACTIVE_HIGH", e;
}({}), Ui = /* @__PURE__ */ w(M, 0, 4, 0), Wi = /* @__PURE__ */ D(M, 0, 5), Gi = /* @__PURE__ */ function(e) {
	return e[e.CODEC2_DEFAULT = 0] = "CODEC2_DEFAULT", e[e.CODEC2_3200 = 1] = "CODEC2_3200", e[e.CODEC2_2400 = 2] = "CODEC2_2400", e[e.CODEC2_1600 = 3] = "CODEC2_1600", e[e.CODEC2_1400 = 4] = "CODEC2_1400", e[e.CODEC2_1300 = 5] = "CODEC2_1300", e[e.CODEC2_1200 = 6] = "CODEC2_1200", e[e.CODEC2_700 = 7] = "CODEC2_700", e[e.CODEC2_700B = 8] = "CODEC2_700B", e;
}({}), Ki = /* @__PURE__ */ w(M, 0, 5, 0), qi = /* @__PURE__ */ D(M, 0, 6), Ji = /* @__PURE__ */ D(M, 0, 7), Yi = /* @__PURE__ */ function(e) {
	return e[e.BAUD_DEFAULT = 0] = "BAUD_DEFAULT", e[e.BAUD_110 = 1] = "BAUD_110", e[e.BAUD_300 = 2] = "BAUD_300", e[e.BAUD_600 = 3] = "BAUD_600", e[e.BAUD_1200 = 4] = "BAUD_1200", e[e.BAUD_2400 = 5] = "BAUD_2400", e[e.BAUD_4800 = 6] = "BAUD_4800", e[e.BAUD_9600 = 7] = "BAUD_9600", e[e.BAUD_19200 = 8] = "BAUD_19200", e[e.BAUD_38400 = 9] = "BAUD_38400", e[e.BAUD_57600 = 10] = "BAUD_57600", e[e.BAUD_115200 = 11] = "BAUD_115200", e[e.BAUD_230400 = 12] = "BAUD_230400", e[e.BAUD_460800 = 13] = "BAUD_460800", e[e.BAUD_576000 = 14] = "BAUD_576000", e[e.BAUD_921600 = 15] = "BAUD_921600", e;
}({}), Xi = /* @__PURE__ */ w(M, 0, 7, 0), Zi = /* @__PURE__ */ function(e) {
	return e[e.DEFAULT = 0] = "DEFAULT", e[e.SIMPLE = 1] = "SIMPLE", e[e.PROTO = 2] = "PROTO", e[e.TEXTMSG = 3] = "TEXTMSG", e[e.NMEA = 4] = "NMEA", e[e.CALTOPO = 5] = "CALTOPO", e[e.WS85 = 6] = "WS85", e[e.VE_DIRECT = 7] = "VE_DIRECT", e;
}({}), Qi = /* @__PURE__ */ w(M, 0, 7, 1), $i = /* @__PURE__ */ D(M, 0, 8), ea = /* @__PURE__ */ D(M, 0, 9), ta = /* @__PURE__ */ D(M, 0, 10), na = /* @__PURE__ */ D(M, 0, 11), ra = /* @__PURE__ */ D(M, 0, 12), ia = /* @__PURE__ */ function(e) {
	return e[e.NONE = 0] = "NONE", e[e.UP = 17] = "UP", e[e.DOWN = 18] = "DOWN", e[e.LEFT = 19] = "LEFT", e[e.RIGHT = 20] = "RIGHT", e[e.SELECT = 10] = "SELECT", e[e.BACK = 27] = "BACK", e[e.CANCEL = 24] = "CANCEL", e;
}({}), aa = /* @__PURE__ */ w(M, 0, 12, 0), oa = /* @__PURE__ */ D(M, 0, 13), sa = /* @__PURE__ */ D(M, 1), ca = /* @__PURE__ */ function(e) {
	return e[e.UNKNOWN = 0] = "UNKNOWN", e[e.DIGITAL_READ = 1] = "DIGITAL_READ", e[e.DIGITAL_WRITE = 2] = "DIGITAL_WRITE", e;
}({}), la = /* @__PURE__ */ w(M, 0), N = m({
	PortNum: () => da,
	PortNumSchema: () => fa,
	file_portnums: () => ua
}), ua = /* @__PURE__ */ E("Cg5wb3J0bnVtcy5wcm90bxIKbWVzaHRhc3RpYyrlBAoHUG9ydE51bRIPCgtVTktOT1dOX0FQUBAAEhQKEFRFWFRfTUVTU0FHRV9BUFAQARIXChNSRU1PVEVfSEFSRFdBUkVfQVBQEAISEAoMUE9TSVRJT05fQVBQEAMSEAoMTk9ERUlORk9fQVBQEAQSDwoLUk9VVElOR19BUFAQBRINCglBRE1JTl9BUFAQBhIfChtURVhUX01FU1NBR0VfQ09NUFJFU1NFRF9BUFAQBxIQCgxXQVlQT0lOVF9BUFAQCBINCglBVURJT19BUFAQCRIYChRERVRFQ1RJT05fU0VOU09SX0FQUBAKEg0KCUFMRVJUX0FQUBALEhgKFEtFWV9WRVJJRklDQVRJT05fQVBQEAwSDQoJUkVQTFlfQVBQECASEQoNSVBfVFVOTkVMX0FQUBAhEhIKDlBBWENPVU5URVJfQVBQECISDgoKU0VSSUFMX0FQUBBAEhUKEVNUT1JFX0ZPUldBUkRfQVBQEEESEgoOUkFOR0VfVEVTVF9BUFAQQhIRCg1URUxFTUVUUllfQVBQEEMSCwoHWlBTX0FQUBBEEhEKDVNJTVVMQVRPUl9BUFAQRRISCg5UUkFDRVJPVVRFX0FQUBBGEhQKEE5FSUdIQk9SSU5GT19BUFAQRxIPCgtBVEFLX1BMVUdJThBIEhIKDk1BUF9SRVBPUlRfQVBQEEkSEwoPUE9XRVJTVFJFU1NfQVBQEEoSGAoUUkVUSUNVTFVNX1RVTk5FTF9BUFAQTBIQCgtQUklWQVRFX0FQUBCAAhITCg5BVEFLX0ZPUldBUkRFUhCBAhIICgNNQVgQ/wNCXQoTY29tLmdlZWtzdmlsbGUubWVzaEIIUG9ydG51bXNaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), da = /* @__PURE__ */ function(e) {
	return e[e.UNKNOWN_APP = 0] = "UNKNOWN_APP", e[e.TEXT_MESSAGE_APP = 1] = "TEXT_MESSAGE_APP", e[e.REMOTE_HARDWARE_APP = 2] = "REMOTE_HARDWARE_APP", e[e.POSITION_APP = 3] = "POSITION_APP", e[e.NODEINFO_APP = 4] = "NODEINFO_APP", e[e.ROUTING_APP = 5] = "ROUTING_APP", e[e.ADMIN_APP = 6] = "ADMIN_APP", e[e.TEXT_MESSAGE_COMPRESSED_APP = 7] = "TEXT_MESSAGE_COMPRESSED_APP", e[e.WAYPOINT_APP = 8] = "WAYPOINT_APP", e[e.AUDIO_APP = 9] = "AUDIO_APP", e[e.DETECTION_SENSOR_APP = 10] = "DETECTION_SENSOR_APP", e[e.ALERT_APP = 11] = "ALERT_APP", e[e.KEY_VERIFICATION_APP = 12] = "KEY_VERIFICATION_APP", e[e.REPLY_APP = 32] = "REPLY_APP", e[e.IP_TUNNEL_APP = 33] = "IP_TUNNEL_APP", e[e.PAXCOUNTER_APP = 34] = "PAXCOUNTER_APP", e[e.SERIAL_APP = 64] = "SERIAL_APP", e[e.STORE_FORWARD_APP = 65] = "STORE_FORWARD_APP", e[e.RANGE_TEST_APP = 66] = "RANGE_TEST_APP", e[e.TELEMETRY_APP = 67] = "TELEMETRY_APP", e[e.ZPS_APP = 68] = "ZPS_APP", e[e.SIMULATOR_APP = 69] = "SIMULATOR_APP", e[e.TRACEROUTE_APP = 70] = "TRACEROUTE_APP", e[e.NEIGHBORINFO_APP = 71] = "NEIGHBORINFO_APP", e[e.ATAK_PLUGIN = 72] = "ATAK_PLUGIN", e[e.MAP_REPORT_APP = 73] = "MAP_REPORT_APP", e[e.POWERSTRESS_APP = 74] = "POWERSTRESS_APP", e[e.RETICULUM_TUNNEL_APP = 76] = "RETICULUM_TUNNEL_APP", e[e.PRIVATE_APP = 256] = "PRIVATE_APP", e[e.ATAK_FORWARDER = 257] = "ATAK_FORWARDER", e[e.MAX = 511] = "MAX", e;
}({}), fa = /* @__PURE__ */ w(ua, 0), pa = m({
	AirQualityMetricsSchema: () => _a,
	DeviceMetricsSchema: () => ma,
	EnvironmentMetricsSchema: () => ha,
	HealthMetricsSchema: () => ya,
	HostMetricsSchema: () => ba,
	LocalStatsSchema: () => va,
	Nau7802ConfigSchema: () => Sa,
	PowerMetricsSchema: () => ga,
	TelemetrySchema: () => xa,
	TelemetrySensorType: () => Ca,
	TelemetrySensorTypeSchema: () => wa,
	file_telemetry: () => P
}), P = /* @__PURE__ */ E("Cg90ZWxlbWV0cnkucHJvdG8SCm1lc2h0YXN0aWMi8wEKDURldmljZU1ldHJpY3MSGgoNYmF0dGVyeV9sZXZlbBgBIAEoDUgAiAEBEhQKB3ZvbHRhZ2UYAiABKAJIAYgBARIgChNjaGFubmVsX3V0aWxpemF0aW9uGAMgASgCSAKIAQESGAoLYWlyX3V0aWxfdHgYBCABKAJIA4gBARIbCg51cHRpbWVfc2Vjb25kcxgFIAEoDUgEiAEBQhAKDl9iYXR0ZXJ5X2xldmVsQgoKCF92b2x0YWdlQhYKFF9jaGFubmVsX3V0aWxpemF0aW9uQg4KDF9haXJfdXRpbF90eEIRCg9fdXB0aW1lX3NlY29uZHMiggcKEkVudmlyb25tZW50TWV0cmljcxIYCgt0ZW1wZXJhdHVyZRgBIAEoAkgAiAEBEh4KEXJlbGF0aXZlX2h1bWlkaXR5GAIgASgCSAGIAQESIAoTYmFyb21ldHJpY19wcmVzc3VyZRgDIAEoAkgCiAEBEhsKDmdhc19yZXNpc3RhbmNlGAQgASgCSAOIAQESFAoHdm9sdGFnZRgFIAEoAkgEiAEBEhQKB2N1cnJlbnQYBiABKAJIBYgBARIQCgNpYXEYByABKA1IBogBARIVCghkaXN0YW5jZRgIIAEoAkgHiAEBEhAKA2x1eBgJIAEoAkgIiAEBEhYKCXdoaXRlX2x1eBgKIAEoAkgJiAEBEhMKBmlyX2x1eBgLIAEoAkgKiAEBEhMKBnV2X2x1eBgMIAEoAkgLiAEBEhsKDndpbmRfZGlyZWN0aW9uGA0gASgNSAyIAQESFwoKd2luZF9zcGVlZBgOIAEoAkgNiAEBEhMKBndlaWdodBgPIAEoAkgOiAEBEhYKCXdpbmRfZ3VzdBgQIAEoAkgPiAEBEhYKCXdpbmRfbHVsbBgRIAEoAkgQiAEBEhYKCXJhZGlhdGlvbhgSIAEoAkgRiAEBEhgKC3JhaW5mYWxsXzFoGBMgASgCSBKIAQESGQoMcmFpbmZhbGxfMjRoGBQgASgCSBOIAQESGgoNc29pbF9tb2lzdHVyZRgVIAEoDUgUiAEBEh0KEHNvaWxfdGVtcGVyYXR1cmUYFiABKAJIFYgBAUIOCgxfdGVtcGVyYXR1cmVCFAoSX3JlbGF0aXZlX2h1bWlkaXR5QhYKFF9iYXJvbWV0cmljX3ByZXNzdXJlQhEKD19nYXNfcmVzaXN0YW5jZUIKCghfdm9sdGFnZUIKCghfY3VycmVudEIGCgRfaWFxQgsKCV9kaXN0YW5jZUIGCgRfbHV4QgwKCl93aGl0ZV9sdXhCCQoHX2lyX2x1eEIJCgdfdXZfbHV4QhEKD193aW5kX2RpcmVjdGlvbkINCgtfd2luZF9zcGVlZEIJCgdfd2VpZ2h0QgwKCl93aW5kX2d1c3RCDAoKX3dpbmRfbHVsbEIMCgpfcmFkaWF0aW9uQg4KDF9yYWluZmFsbF8xaEIPCg1fcmFpbmZhbGxfMjRoQhAKDl9zb2lsX21vaXN0dXJlQhMKEV9zb2lsX3RlbXBlcmF0dXJlIooCCgxQb3dlck1ldHJpY3MSGAoLY2gxX3ZvbHRhZ2UYASABKAJIAIgBARIYCgtjaDFfY3VycmVudBgCIAEoAkgBiAEBEhgKC2NoMl92b2x0YWdlGAMgASgCSAKIAQESGAoLY2gyX2N1cnJlbnQYBCABKAJIA4gBARIYCgtjaDNfdm9sdGFnZRgFIAEoAkgEiAEBEhgKC2NoM19jdXJyZW50GAYgASgCSAWIAQFCDgoMX2NoMV92b2x0YWdlQg4KDF9jaDFfY3VycmVudEIOCgxfY2gyX3ZvbHRhZ2VCDgoMX2NoMl9jdXJyZW50Qg4KDF9jaDNfdm9sdGFnZUIOCgxfY2gzX2N1cnJlbnQihQUKEUFpclF1YWxpdHlNZXRyaWNzEhoKDXBtMTBfc3RhbmRhcmQYASABKA1IAIgBARIaCg1wbTI1X3N0YW5kYXJkGAIgASgNSAGIAQESGwoOcG0xMDBfc3RhbmRhcmQYAyABKA1IAogBARIfChJwbTEwX2Vudmlyb25tZW50YWwYBCABKA1IA4gBARIfChJwbTI1X2Vudmlyb25tZW50YWwYBSABKA1IBIgBARIgChNwbTEwMF9lbnZpcm9ubWVudGFsGAYgASgNSAWIAQESGwoOcGFydGljbGVzXzAzdW0YByABKA1IBogBARIbCg5wYXJ0aWNsZXNfMDV1bRgIIAEoDUgHiAEBEhsKDnBhcnRpY2xlc18xMHVtGAkgASgNSAiIAQESGwoOcGFydGljbGVzXzI1dW0YCiABKA1ICYgBARIbCg5wYXJ0aWNsZXNfNTB1bRgLIAEoDUgKiAEBEhwKD3BhcnRpY2xlc18xMDB1bRgMIAEoDUgLiAEBEhAKA2NvMhgNIAEoDUgMiAEBQhAKDl9wbTEwX3N0YW5kYXJkQhAKDl9wbTI1X3N0YW5kYXJkQhEKD19wbTEwMF9zdGFuZGFyZEIVChNfcG0xMF9lbnZpcm9ubWVudGFsQhUKE19wbTI1X2Vudmlyb25tZW50YWxCFgoUX3BtMTAwX2Vudmlyb25tZW50YWxCEQoPX3BhcnRpY2xlc18wM3VtQhEKD19wYXJ0aWNsZXNfMDV1bUIRCg9fcGFydGljbGVzXzEwdW1CEQoPX3BhcnRpY2xlc18yNXVtQhEKD19wYXJ0aWNsZXNfNTB1bUISChBfcGFydGljbGVzXzEwMHVtQgYKBF9jbzIi0gIKCkxvY2FsU3RhdHMSFgoOdXB0aW1lX3NlY29uZHMYASABKA0SGwoTY2hhbm5lbF91dGlsaXphdGlvbhgCIAEoAhITCgthaXJfdXRpbF90eBgDIAEoAhIWCg5udW1fcGFja2V0c190eBgEIAEoDRIWCg5udW1fcGFja2V0c19yeBgFIAEoDRIaChJudW1fcGFja2V0c19yeF9iYWQYBiABKA0SGAoQbnVtX29ubGluZV9ub2RlcxgHIAEoDRIXCg9udW1fdG90YWxfbm9kZXMYCCABKA0SEwoLbnVtX3J4X2R1cGUYCSABKA0SFAoMbnVtX3R4X3JlbGF5GAogASgNEh0KFW51bV90eF9yZWxheV9jYW5jZWxlZBgLIAEoDRIYChBoZWFwX3RvdGFsX2J5dGVzGAwgASgNEhcKD2hlYXBfZnJlZV9ieXRlcxgNIAEoDSJ7Cg1IZWFsdGhNZXRyaWNzEhYKCWhlYXJ0X2JwbRgBIAEoDUgAiAEBEhEKBHNwTzIYAiABKA1IAYgBARIYCgt0ZW1wZXJhdHVyZRgDIAEoAkgCiAEBQgwKCl9oZWFydF9icG1CBwoFX3NwTzJCDgoMX3RlbXBlcmF0dXJlIpECCgtIb3N0TWV0cmljcxIWCg51cHRpbWVfc2Vjb25kcxgBIAEoDRIVCg1mcmVlbWVtX2J5dGVzGAIgASgEEhcKD2Rpc2tmcmVlMV9ieXRlcxgDIAEoBBIcCg9kaXNrZnJlZTJfYnl0ZXMYBCABKARIAIgBARIcCg9kaXNrZnJlZTNfYnl0ZXMYBSABKARIAYgBARINCgVsb2FkMRgGIAEoDRINCgVsb2FkNRgHIAEoDRIOCgZsb2FkMTUYCCABKA0SGAoLdXNlcl9zdHJpbmcYCSABKAlIAogBAUISChBfZGlza2ZyZWUyX2J5dGVzQhIKEF9kaXNrZnJlZTNfYnl0ZXNCDgoMX3VzZXJfc3RyaW5nIp4DCglUZWxlbWV0cnkSDAoEdGltZRgBIAEoBxIzCg5kZXZpY2VfbWV0cmljcxgCIAEoCzIZLm1lc2h0YXN0aWMuRGV2aWNlTWV0cmljc0gAEj0KE2Vudmlyb25tZW50X21ldHJpY3MYAyABKAsyHi5tZXNodGFzdGljLkVudmlyb25tZW50TWV0cmljc0gAEjwKE2Fpcl9xdWFsaXR5X21ldHJpY3MYBCABKAsyHS5tZXNodGFzdGljLkFpclF1YWxpdHlNZXRyaWNzSAASMQoNcG93ZXJfbWV0cmljcxgFIAEoCzIYLm1lc2h0YXN0aWMuUG93ZXJNZXRyaWNzSAASLQoLbG9jYWxfc3RhdHMYBiABKAsyFi5tZXNodGFzdGljLkxvY2FsU3RhdHNIABIzCg5oZWFsdGhfbWV0cmljcxgHIAEoCzIZLm1lc2h0YXN0aWMuSGVhbHRoTWV0cmljc0gAEi8KDGhvc3RfbWV0cmljcxgIIAEoCzIXLm1lc2h0YXN0aWMuSG9zdE1ldHJpY3NIAEIJCgd2YXJpYW50Ij4KDU5hdTc4MDJDb25maWcSEgoKemVyb09mZnNldBgBIAEoBRIZChFjYWxpYnJhdGlvbkZhY3RvchgCIAEoAiqsBAoTVGVsZW1ldHJ5U2Vuc29yVHlwZRIQCgxTRU5TT1JfVU5TRVQQABIKCgZCTUUyODAQARIKCgZCTUU2ODAQAhILCgdNQ1A5ODA4EAMSCgoGSU5BMjYwEAQSCgoGSU5BMjE5EAUSCgoGQk1QMjgwEAYSCQoFU0hUQzMQBxIJCgVMUFMyMhAIEgsKB1FNQzYzMTAQCRILCgdRTUk4NjU4EAoSDAoIUU1DNTg4M0wQCxIJCgVTSFQzMRAMEgwKCFBNU0EwMDNJEA0SCwoHSU5BMzIyMRAOEgoKBkJNUDA4NRAPEgwKCFJDV0w5NjIwEBASCQoFU0hUNFgQERIMCghWRU1MNzcwMBASEgwKCE1MWDkwNjMyEBMSCwoHT1BUMzAwMRAUEgwKCExUUjM5MFVWEBUSDgoKVFNMMjU5MTFGThAWEgkKBUFIVDEwEBcSEAoMREZST0JPVF9MQVJLEBgSCwoHTkFVNzgwMhAZEgoKBkJNUDNYWBAaEgwKCElDTTIwOTQ4EBsSDAoITUFYMTcwNDgQHBIRCg1DVVNUT01fU0VOU09SEB0SDAoITUFYMzAxMDIQHhIMCghNTFg5MDYxNBAfEgkKBVNDRDRYECASCwoHUkFEU0VOUxAhEgoKBklOQTIyNhAiEhAKDERGUk9CT1RfUkFJThAjEgoKBkRQUzMxMBAkEgwKCFJBSzEyMDM1ECUSDAoITUFYMTcyNjEQJhILCgdQQ1QyMDc1ECdCZAoTY29tLmdlZWtzdmlsbGUubWVzaEIPVGVsZW1ldHJ5UHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), ma = /* @__PURE__ */ D(P, 0), ha = /* @__PURE__ */ D(P, 1), ga = /* @__PURE__ */ D(P, 2), _a = /* @__PURE__ */ D(P, 3), va = /* @__PURE__ */ D(P, 4), ya = /* @__PURE__ */ D(P, 5), ba = /* @__PURE__ */ D(P, 6), xa = /* @__PURE__ */ D(P, 7), Sa = /* @__PURE__ */ D(P, 8), Ca = /* @__PURE__ */ function(e) {
	return e[e.SENSOR_UNSET = 0] = "SENSOR_UNSET", e[e.BME280 = 1] = "BME280", e[e.BME680 = 2] = "BME680", e[e.MCP9808 = 3] = "MCP9808", e[e.INA260 = 4] = "INA260", e[e.INA219 = 5] = "INA219", e[e.BMP280 = 6] = "BMP280", e[e.SHTC3 = 7] = "SHTC3", e[e.LPS22 = 8] = "LPS22", e[e.QMC6310 = 9] = "QMC6310", e[e.QMI8658 = 10] = "QMI8658", e[e.QMC5883L = 11] = "QMC5883L", e[e.SHT31 = 12] = "SHT31", e[e.PMSA003I = 13] = "PMSA003I", e[e.INA3221 = 14] = "INA3221", e[e.BMP085 = 15] = "BMP085", e[e.RCWL9620 = 16] = "RCWL9620", e[e.SHT4X = 17] = "SHT4X", e[e.VEML7700 = 18] = "VEML7700", e[e.MLX90632 = 19] = "MLX90632", e[e.OPT3001 = 20] = "OPT3001", e[e.LTR390UV = 21] = "LTR390UV", e[e.TSL25911FN = 22] = "TSL25911FN", e[e.AHT10 = 23] = "AHT10", e[e.DFROBOT_LARK = 24] = "DFROBOT_LARK", e[e.NAU7802 = 25] = "NAU7802", e[e.BMP3XX = 26] = "BMP3XX", e[e.ICM20948 = 27] = "ICM20948", e[e.MAX17048 = 28] = "MAX17048", e[e.CUSTOM_SENSOR = 29] = "CUSTOM_SENSOR", e[e.MAX30102 = 30] = "MAX30102", e[e.MLX90614 = 31] = "MLX90614", e[e.SCD4X = 32] = "SCD4X", e[e.RADSENS = 33] = "RADSENS", e[e.INA226 = 34] = "INA226", e[e.DFROBOT_RAIN = 35] = "DFROBOT_RAIN", e[e.DPS310 = 36] = "DPS310", e[e.RAK12035 = 37] = "RAK12035", e[e.MAX17261 = 38] = "MAX17261", e[e.PCT2075 = 39] = "PCT2075", e;
}({}), wa = /* @__PURE__ */ w(P, 0), F = m({
	XModemSchema: () => Ea,
	XModem_Control: () => Da,
	XModem_ControlSchema: () => Oa,
	file_xmodem: () => Ta
}), Ta = /* @__PURE__ */ E("Cgx4bW9kZW0ucHJvdG8SCm1lc2h0YXN0aWMitgEKBlhNb2RlbRIrCgdjb250cm9sGAEgASgOMhoubWVzaHRhc3RpYy5YTW9kZW0uQ29udHJvbBILCgNzZXEYAiABKA0SDQoFY3JjMTYYAyABKA0SDgoGYnVmZmVyGAQgASgMIlMKB0NvbnRyb2wSBwoDTlVMEAASBwoDU09IEAESBwoDU1RYEAISBwoDRU9UEAQSBwoDQUNLEAYSBwoDTkFLEBUSBwoDQ0FOEBgSCQoFQ1RSTFoQGkJhChNjb20uZ2Vla3N2aWxsZS5tZXNoQgxYbW9kZW1Qcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), Ea = /* @__PURE__ */ D(Ta, 0), Da = /* @__PURE__ */ function(e) {
	return e[e.NUL = 0] = "NUL", e[e.SOH = 1] = "SOH", e[e.STX = 2] = "STX", e[e.EOT = 4] = "EOT", e[e.ACK = 6] = "ACK", e[e.NAK = 21] = "NAK", e[e.CAN = 24] = "CAN", e[e.CTRLZ = 26] = "CTRLZ", e;
}({}), Oa = /* @__PURE__ */ w(Ta, 0, 0), I = m({
	ChunkedPayloadResponseSchema: () => vo,
	ChunkedPayloadSchema: () => go,
	ClientNotificationSchema: () => to,
	CompressedSchema: () => lo,
	Constants: () => xo,
	ConstantsSchema: () => So,
	CriticalErrorCode: () => Co,
	CriticalErrorCodeSchema: () => wo,
	DataSchema: () => za,
	DeviceMetadataSchema: () => po,
	DuplicatedPublicKeySchema: () => ao,
	ExcludedModules: () => To,
	ExcludedModulesSchema: () => Eo,
	FileInfoSchema: () => so,
	FromRadioSchema: () => eo,
	HardwareModel: () => yo,
	HardwareModelSchema: () => bo,
	HeartbeatSchema: () => mo,
	KeyVerificationFinalSchema: () => io,
	KeyVerificationNumberInformSchema: () => no,
	KeyVerificationNumberRequestSchema: () => ro,
	KeyVerificationSchema: () => Ba,
	LogRecordSchema: () => Xa,
	LogRecord_Level: () => Za,
	LogRecord_LevelSchema: () => Qa,
	LowEntropyKeySchema: () => oo,
	MeshPacketSchema: () => Ua,
	MeshPacket_Delayed: () => Ka,
	MeshPacket_DelayedSchema: () => qa,
	MeshPacket_Priority: () => Wa,
	MeshPacket_PrioritySchema: () => Ga,
	MqttClientProxyMessageSchema: () => Ha,
	MyNodeInfoSchema: () => Ya,
	NeighborInfoSchema: () => uo,
	NeighborSchema: () => fo,
	NodeInfoSchema: () => Ja,
	NodeRemoteHardwarePinSchema: () => ho,
	PositionSchema: () => ka,
	Position_AltSource: () => Ma,
	Position_AltSourceSchema: () => Na,
	Position_LocSource: () => Aa,
	Position_LocSourceSchema: () => ja,
	QueueStatusSchema: () => $a,
	RouteDiscoverySchema: () => Fa,
	RoutingSchema: () => Ia,
	Routing_Error: () => La,
	Routing_ErrorSchema: () => Ra,
	ToRadioSchema: () => co,
	UserSchema: () => Pa,
	WaypointSchema: () => Va,
	file_mesh: () => L,
	resend_chunksSchema: () => _o
}), L = /* @__PURE__ */ E("CgptZXNoLnByb3RvEgptZXNodGFzdGljIocHCghQb3NpdGlvbhIXCgpsYXRpdHVkZV9pGAEgASgPSACIAQESGAoLbG9uZ2l0dWRlX2kYAiABKA9IAYgBARIVCghhbHRpdHVkZRgDIAEoBUgCiAEBEgwKBHRpbWUYBCABKAcSNwoPbG9jYXRpb25fc291cmNlGAUgASgOMh4ubWVzaHRhc3RpYy5Qb3NpdGlvbi5Mb2NTb3VyY2USNwoPYWx0aXR1ZGVfc291cmNlGAYgASgOMh4ubWVzaHRhc3RpYy5Qb3NpdGlvbi5BbHRTb3VyY2USEQoJdGltZXN0YW1wGAcgASgHEh8KF3RpbWVzdGFtcF9taWxsaXNfYWRqdXN0GAggASgFEhkKDGFsdGl0dWRlX2hhZRgJIAEoEUgDiAEBEigKG2FsdGl0dWRlX2dlb2lkYWxfc2VwYXJhdGlvbhgKIAEoEUgEiAEBEgwKBFBET1AYCyABKA0SDAoESERPUBgMIAEoDRIMCgRWRE9QGA0gASgNEhQKDGdwc19hY2N1cmFjeRgOIAEoDRIZCgxncm91bmRfc3BlZWQYDyABKA1IBYgBARIZCgxncm91bmRfdHJhY2sYECABKA1IBogBARITCgtmaXhfcXVhbGl0eRgRIAEoDRIQCghmaXhfdHlwZRgSIAEoDRIUCgxzYXRzX2luX3ZpZXcYEyABKA0SEQoJc2Vuc29yX2lkGBQgASgNEhMKC25leHRfdXBkYXRlGBUgASgNEhIKCnNlcV9udW1iZXIYFiABKA0SFgoOcHJlY2lzaW9uX2JpdHMYFyABKA0iTgoJTG9jU291cmNlEg0KCUxPQ19VTlNFVBAAEg4KCkxPQ19NQU5VQUwQARIQCgxMT0NfSU5URVJOQUwQAhIQCgxMT0NfRVhURVJOQUwQAyJiCglBbHRTb3VyY2USDQoJQUxUX1VOU0VUEAASDgoKQUxUX01BTlVBTBABEhAKDEFMVF9JTlRFUk5BTBACEhAKDEFMVF9FWFRFUk5BTBADEhIKDkFMVF9CQVJPTUVUUklDEARCDQoLX2xhdGl0dWRlX2lCDgoMX2xvbmdpdHVkZV9pQgsKCV9hbHRpdHVkZUIPCg1fYWx0aXR1ZGVfaGFlQh4KHF9hbHRpdHVkZV9nZW9pZGFsX3NlcGFyYXRpb25CDwoNX2dyb3VuZF9zcGVlZEIPCg1fZ3JvdW5kX3RyYWNrIooCCgRVc2VyEgoKAmlkGAEgASgJEhEKCWxvbmdfbmFtZRgCIAEoCRISCgpzaG9ydF9uYW1lGAMgASgJEhMKB21hY2FkZHIYBCABKAxCAhgBEisKCGh3X21vZGVsGAUgASgOMhkubWVzaHRhc3RpYy5IYXJkd2FyZU1vZGVsEhMKC2lzX2xpY2Vuc2VkGAYgASgIEjIKBHJvbGUYByABKA4yJC5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWcuUm9sZRISCgpwdWJsaWNfa2V5GAggASgMEhwKD2lzX3VubWVzc2FnYWJsZRgJIAEoCEgAiAEBQhIKEF9pc191bm1lc3NhZ2FibGUiWgoOUm91dGVEaXNjb3ZlcnkSDQoFcm91dGUYASADKAcSEwoLc25yX3Rvd2FyZHMYAiADKAUSEgoKcm91dGVfYmFjaxgDIAMoBxIQCghzbnJfYmFjaxgEIAMoBSLiAwoHUm91dGluZxIzCg1yb3V0ZV9yZXF1ZXN0GAEgASgLMhoubWVzaHRhc3RpYy5Sb3V0ZURpc2NvdmVyeUgAEjEKC3JvdXRlX3JlcGx5GAIgASgLMhoubWVzaHRhc3RpYy5Sb3V0ZURpc2NvdmVyeUgAEjEKDGVycm9yX3JlYXNvbhgDIAEoDjIZLm1lc2h0YXN0aWMuUm91dGluZy5FcnJvckgAIrACCgVFcnJvchIICgROT05FEAASDAoITk9fUk9VVEUQARILCgdHT1RfTkFLEAISCwoHVElNRU9VVBADEhAKDE5PX0lOVEVSRkFDRRAEEhIKDk1BWF9SRVRSQU5TTUlUEAUSDgoKTk9fQ0hBTk5FTBAGEg0KCVRPT19MQVJHRRAHEg8KC05PX1JFU1BPTlNFEAgSFAoQRFVUWV9DWUNMRV9MSU1JVBAJEg8KC0JBRF9SRVFVRVNUECASEgoOTk9UX0FVVEhPUklaRUQQIRIOCgpQS0lfRkFJTEVEECISFgoSUEtJX1VOS05PV05fUFVCS0VZECMSGQoVQURNSU5fQkFEX1NFU1NJT05fS0VZECQSIQodQURNSU5fUFVCTElDX0tFWV9VTkFVVEhPUklaRUQQJUIJCgd2YXJpYW50IssBCgREYXRhEiQKB3BvcnRudW0YASABKA4yEy5tZXNodGFzdGljLlBvcnROdW0SDwoHcGF5bG9hZBgCIAEoDBIVCg13YW50X3Jlc3BvbnNlGAMgASgIEgwKBGRlc3QYBCABKAcSDgoGc291cmNlGAUgASgHEhIKCnJlcXVlc3RfaWQYBiABKAcSEAoIcmVwbHlfaWQYByABKAcSDQoFZW1vamkYCCABKAcSFQoIYml0ZmllbGQYCSABKA1IAIgBAUILCglfYml0ZmllbGQiPgoPS2V5VmVyaWZpY2F0aW9uEg0KBW5vbmNlGAEgASgEEg0KBWhhc2gxGAIgASgMEg0KBWhhc2gyGAMgASgMIrwBCghXYXlwb2ludBIKCgJpZBgBIAEoDRIXCgpsYXRpdHVkZV9pGAIgASgPSACIAQESGAoLbG9uZ2l0dWRlX2kYAyABKA9IAYgBARIOCgZleHBpcmUYBCABKA0SEQoJbG9ja2VkX3RvGAUgASgNEgwKBG5hbWUYBiABKAkSEwoLZGVzY3JpcHRpb24YByABKAkSDAoEaWNvbhgIIAEoB0INCgtfbGF0aXR1ZGVfaUIOCgxfbG9uZ2l0dWRlX2kibAoWTXF0dENsaWVudFByb3h5TWVzc2FnZRINCgV0b3BpYxgBIAEoCRIOCgRkYXRhGAIgASgMSAASDgoEdGV4dBgDIAEoCUgAEhAKCHJldGFpbmVkGAQgASgIQhEKD3BheWxvYWRfdmFyaWFudCKbBQoKTWVzaFBhY2tldBIMCgRmcm9tGAEgASgHEgoKAnRvGAIgASgHEg8KB2NoYW5uZWwYAyABKA0SIwoHZGVjb2RlZBgEIAEoCzIQLm1lc2h0YXN0aWMuRGF0YUgAEhMKCWVuY3J5cHRlZBgFIAEoDEgAEgoKAmlkGAYgASgHEg8KB3J4X3RpbWUYByABKAcSDgoGcnhfc25yGAggASgCEhEKCWhvcF9saW1pdBgJIAEoDRIQCgh3YW50X2FjaxgKIAEoCBIxCghwcmlvcml0eRgLIAEoDjIfLm1lc2h0YXN0aWMuTWVzaFBhY2tldC5Qcmlvcml0eRIPCgdyeF9yc3NpGAwgASgFEjMKB2RlbGF5ZWQYDSABKA4yHi5tZXNodGFzdGljLk1lc2hQYWNrZXQuRGVsYXllZEICGAESEAoIdmlhX21xdHQYDiABKAgSEQoJaG9wX3N0YXJ0GA8gASgNEhIKCnB1YmxpY19rZXkYECABKAwSFQoNcGtpX2VuY3J5cHRlZBgRIAEoCBIQCghuZXh0X2hvcBgSIAEoDRISCgpyZWxheV9ub2RlGBMgASgNEhAKCHR4X2FmdGVyGBQgASgNIn4KCFByaW9yaXR5EgkKBVVOU0VUEAASBwoDTUlOEAESDgoKQkFDS0dST1VORBAKEgsKB0RFRkFVTFQQQBIMCghSRUxJQUJMRRBGEgwKCFJFU1BPTlNFEFASCAoESElHSBBkEgkKBUFMRVJUEG4SBwoDQUNLEHgSBwoDTUFYEH8iQgoHRGVsYXllZBIMCghOT19ERUxBWRAAEhUKEURFTEFZRURfQlJPQURDQVNUEAESEgoOREVMQVlFRF9ESVJFQ1QQAkIRCg9wYXlsb2FkX3ZhcmlhbnQixwIKCE5vZGVJbmZvEgsKA251bRgBIAEoDRIeCgR1c2VyGAIgASgLMhAubWVzaHRhc3RpYy5Vc2VyEiYKCHBvc2l0aW9uGAMgASgLMhQubWVzaHRhc3RpYy5Qb3NpdGlvbhILCgNzbnIYBCABKAISEgoKbGFzdF9oZWFyZBgFIAEoBxIxCg5kZXZpY2VfbWV0cmljcxgGIAEoCzIZLm1lc2h0YXN0aWMuRGV2aWNlTWV0cmljcxIPCgdjaGFubmVsGAcgASgNEhAKCHZpYV9tcXR0GAggASgIEhYKCWhvcHNfYXdheRgJIAEoDUgAiAEBEhMKC2lzX2Zhdm9yaXRlGAogASgIEhIKCmlzX2lnbm9yZWQYCyABKAgSIAoYaXNfa2V5X21hbnVhbGx5X3ZlcmlmaWVkGAwgASgIQgwKCl9ob3BzX2F3YXkidAoKTXlOb2RlSW5mbxITCgtteV9ub2RlX251bRgBIAEoDRIUCgxyZWJvb3RfY291bnQYCCABKA0SFwoPbWluX2FwcF92ZXJzaW9uGAsgASgNEhEKCWRldmljZV9pZBgMIAEoDBIPCgdwaW9fZW52GA0gASgJIsABCglMb2dSZWNvcmQSDwoHbWVzc2FnZRgBIAEoCRIMCgR0aW1lGAIgASgHEg4KBnNvdXJjZRgDIAEoCRIqCgVsZXZlbBgEIAEoDjIbLm1lc2h0YXN0aWMuTG9nUmVjb3JkLkxldmVsIlgKBUxldmVsEgkKBVVOU0VUEAASDAoIQ1JJVElDQUwQMhIJCgVFUlJPUhAoEgsKB1dBUk5JTkcQHhIICgRJTkZPEBQSCQoFREVCVUcQChIJCgVUUkFDRRAFIlAKC1F1ZXVlU3RhdHVzEgsKA3JlcxgBIAEoBRIMCgRmcmVlGAIgASgNEg4KBm1heGxlbhgDIAEoDRIWCg5tZXNoX3BhY2tldF9pZBgEIAEoDSL5BQoJRnJvbVJhZGlvEgoKAmlkGAEgASgNEigKBnBhY2tldBgCIAEoCzIWLm1lc2h0YXN0aWMuTWVzaFBhY2tldEgAEikKB215X2luZm8YAyABKAsyFi5tZXNodGFzdGljLk15Tm9kZUluZm9IABIpCglub2RlX2luZm8YBCABKAsyFC5tZXNodGFzdGljLk5vZGVJbmZvSAASJAoGY29uZmlnGAUgASgLMhIubWVzaHRhc3RpYy5Db25maWdIABIrCgpsb2dfcmVjb3JkGAYgASgLMhUubWVzaHRhc3RpYy5Mb2dSZWNvcmRIABIcChJjb25maWdfY29tcGxldGVfaWQYByABKA1IABISCghyZWJvb3RlZBgIIAEoCEgAEjAKDG1vZHVsZUNvbmZpZxgJIAEoCzIYLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnSAASJgoHY2hhbm5lbBgKIAEoCzITLm1lc2h0YXN0aWMuQ2hhbm5lbEgAEi4KC3F1ZXVlU3RhdHVzGAsgASgLMhcubWVzaHRhc3RpYy5RdWV1ZVN0YXR1c0gAEioKDHhtb2RlbVBhY2tldBgMIAEoCzISLm1lc2h0YXN0aWMuWE1vZGVtSAASLgoIbWV0YWRhdGEYDSABKAsyGi5tZXNodGFzdGljLkRldmljZU1ldGFkYXRhSAASRAoWbXF0dENsaWVudFByb3h5TWVzc2FnZRgOIAEoCzIiLm1lc2h0YXN0aWMuTXF0dENsaWVudFByb3h5TWVzc2FnZUgAEigKCGZpbGVJbmZvGA8gASgLMhQubWVzaHRhc3RpYy5GaWxlSW5mb0gAEjwKEmNsaWVudE5vdGlmaWNhdGlvbhgQIAEoCzIeLm1lc2h0YXN0aWMuQ2xpZW50Tm90aWZpY2F0aW9uSAASNAoOZGV2aWNldWlDb25maWcYESABKAsyGi5tZXNodGFzdGljLkRldmljZVVJQ29uZmlnSABCEQoPcGF5bG9hZF92YXJpYW50IvoDChJDbGllbnROb3RpZmljYXRpb24SFQoIcmVwbHlfaWQYASABKA1IAYgBARIMCgR0aW1lGAIgASgHEioKBWxldmVsGAMgASgOMhsubWVzaHRhc3RpYy5Mb2dSZWNvcmQuTGV2ZWwSDwoHbWVzc2FnZRgEIAEoCRJRCh5rZXlfdmVyaWZpY2F0aW9uX251bWJlcl9pbmZvcm0YCyABKAsyJy5tZXNodGFzdGljLktleVZlcmlmaWNhdGlvbk51bWJlckluZm9ybUgAElMKH2tleV92ZXJpZmljYXRpb25fbnVtYmVyX3JlcXVlc3QYDCABKAsyKC5tZXNodGFzdGljLktleVZlcmlmaWNhdGlvbk51bWJlclJlcXVlc3RIABJCChZrZXlfdmVyaWZpY2F0aW9uX2ZpbmFsGA0gASgLMiAubWVzaHRhc3RpYy5LZXlWZXJpZmljYXRpb25GaW5hbEgAEkAKFWR1cGxpY2F0ZWRfcHVibGljX2tleRgOIAEoCzIfLm1lc2h0YXN0aWMuRHVwbGljYXRlZFB1YmxpY0tleUgAEjQKD2xvd19lbnRyb3B5X2tleRgPIAEoCzIZLm1lc2h0YXN0aWMuTG93RW50cm9weUtleUgAQhEKD3BheWxvYWRfdmFyaWFudEILCglfcmVwbHlfaWQiXgobS2V5VmVyaWZpY2F0aW9uTnVtYmVySW5mb3JtEg0KBW5vbmNlGAEgASgEEhcKD3JlbW90ZV9sb25nbmFtZRgCIAEoCRIXCg9zZWN1cml0eV9udW1iZXIYAyABKA0iRgocS2V5VmVyaWZpY2F0aW9uTnVtYmVyUmVxdWVzdBINCgVub25jZRgBIAEoBBIXCg9yZW1vdGVfbG9uZ25hbWUYAiABKAkicQoUS2V5VmVyaWZpY2F0aW9uRmluYWwSDQoFbm9uY2UYASABKAQSFwoPcmVtb3RlX2xvbmduYW1lGAIgASgJEhAKCGlzU2VuZGVyGAMgASgIEh8KF3ZlcmlmaWNhdGlvbl9jaGFyYWN0ZXJzGAQgASgJIhUKE0R1cGxpY2F0ZWRQdWJsaWNLZXkiDwoNTG93RW50cm9weUtleSIxCghGaWxlSW5mbxIRCglmaWxlX25hbWUYASABKAkSEgoKc2l6ZV9ieXRlcxgCIAEoDSKUAgoHVG9SYWRpbxIoCgZwYWNrZXQYASABKAsyFi5tZXNodGFzdGljLk1lc2hQYWNrZXRIABIYCg53YW50X2NvbmZpZ19pZBgDIAEoDUgAEhQKCmRpc2Nvbm5lY3QYBCABKAhIABIqCgx4bW9kZW1QYWNrZXQYBSABKAsyEi5tZXNodGFzdGljLlhNb2RlbUgAEkQKFm1xdHRDbGllbnRQcm94eU1lc3NhZ2UYBiABKAsyIi5tZXNodGFzdGljLk1xdHRDbGllbnRQcm94eU1lc3NhZ2VIABIqCgloZWFydGJlYXQYByABKAsyFS5tZXNodGFzdGljLkhlYXJ0YmVhdEgAQhEKD3BheWxvYWRfdmFyaWFudCJACgpDb21wcmVzc2VkEiQKB3BvcnRudW0YASABKA4yEy5tZXNodGFzdGljLlBvcnROdW0SDAoEZGF0YRgCIAEoDCKHAQoMTmVpZ2hib3JJbmZvEg8KB25vZGVfaWQYASABKA0SFwoPbGFzdF9zZW50X2J5X2lkGAIgASgNEiQKHG5vZGVfYnJvYWRjYXN0X2ludGVydmFsX3NlY3MYAyABKA0SJwoJbmVpZ2hib3JzGAQgAygLMhQubWVzaHRhc3RpYy5OZWlnaGJvciJkCghOZWlnaGJvchIPCgdub2RlX2lkGAEgASgNEgsKA3NuchgCIAEoAhIUCgxsYXN0X3J4X3RpbWUYAyABKAcSJAocbm9kZV9icm9hZGNhc3RfaW50ZXJ2YWxfc2VjcxgEIAEoDSLXAgoORGV2aWNlTWV0YWRhdGESGAoQZmlybXdhcmVfdmVyc2lvbhgBIAEoCRIcChRkZXZpY2Vfc3RhdGVfdmVyc2lvbhgCIAEoDRITCgtjYW5TaHV0ZG93bhgDIAEoCBIPCgdoYXNXaWZpGAQgASgIEhQKDGhhc0JsdWV0b290aBgFIAEoCBITCgtoYXNFdGhlcm5ldBgGIAEoCBIyCgRyb2xlGAcgASgOMiQubWVzaHRhc3RpYy5Db25maWcuRGV2aWNlQ29uZmlnLlJvbGUSFgoOcG9zaXRpb25fZmxhZ3MYCCABKA0SKwoIaHdfbW9kZWwYCSABKA4yGS5tZXNodGFzdGljLkhhcmR3YXJlTW9kZWwSGQoRaGFzUmVtb3RlSGFyZHdhcmUYCiABKAgSDgoGaGFzUEtDGAsgASgIEhgKEGV4Y2x1ZGVkX21vZHVsZXMYDCABKA0iCwoJSGVhcnRiZWF0IlUKFU5vZGVSZW1vdGVIYXJkd2FyZVBpbhIQCghub2RlX251bRgBIAEoDRIqCgNwaW4YAiABKAsyHS5tZXNodGFzdGljLlJlbW90ZUhhcmR3YXJlUGluImUKDkNodW5rZWRQYXlsb2FkEhIKCnBheWxvYWRfaWQYASABKA0SEwoLY2h1bmtfY291bnQYAiABKA0SEwoLY2h1bmtfaW5kZXgYAyABKA0SFQoNcGF5bG9hZF9jaHVuaxgEIAEoDCIfCg1yZXNlbmRfY2h1bmtzEg4KBmNodW5rcxgBIAMoDSKqAQoWQ2h1bmtlZFBheWxvYWRSZXNwb25zZRISCgpwYXlsb2FkX2lkGAEgASgNEhoKEHJlcXVlc3RfdHJhbnNmZXIYAiABKAhIABIZCg9hY2NlcHRfdHJhbnNmZXIYAyABKAhIABIyCg1yZXNlbmRfY2h1bmtzGAQgASgLMhkubWVzaHRhc3RpYy5yZXNlbmRfY2h1bmtzSABCEQoPcGF5bG9hZF92YXJpYW50KvcPCg1IYXJkd2FyZU1vZGVsEgkKBVVOU0VUEAASDAoIVExPUkFfVjIQARIMCghUTE9SQV9WMRACEhIKDlRMT1JBX1YyXzFfMVA2EAMSCQoFVEJFQU0QBBIPCgtIRUxURUNfVjJfMBAFEg4KClRCRUFNX1YwUDcQBhIKCgZUX0VDSE8QBxIQCgxUTE9SQV9WMV8xUDMQCBILCgdSQUs0NjMxEAkSDwoLSEVMVEVDX1YyXzEQChINCglIRUxURUNfVjEQCxIYChRMSUxZR09fVEJFQU1fUzNfQ09SRRAMEgwKCFJBSzExMjAwEA0SCwoHTkFOT19HMRAOEhIKDlRMT1JBX1YyXzFfMVA4EA8SDwoLVExPUkFfVDNfUzMQEBIUChBOQU5PX0cxX0VYUExPUkVSEBESEQoNTkFOT19HMl9VTFRSQRASEg0KCUxPUkFfVFlQRRATEgsKB1dJUEhPTkUQFBIOCgpXSU9fV00xMTEwEBUSCwoHUkFLMjU2MBAWEhMKD0hFTFRFQ19IUlVfMzYwMRAXEhoKFkhFTFRFQ19XSVJFTEVTU19CUklER0UQGBIOCgpTVEFUSU9OX0cxEBkSDAoIUkFLMTEzMTAQGhIUChBTRU5TRUxPUkFfUlAyMDQwEBsSEAoMU0VOU0VMT1JBX1MzEBwSDQoJQ0FOQVJZT05FEB0SDwoLUlAyMDQwX0xPUkEQHhIOCgpTVEFUSU9OX0cyEB8SEQoNTE9SQV9SRUxBWV9WMRAgEg4KCk5SRjUyODQwREsQIRIHCgNQUFIQIhIPCgtHRU5JRUJMT0NLUxAjEhEKDU5SRjUyX1VOS05PV04QJBINCglQT1JURFVJTk8QJRIPCgtBTkRST0lEX1NJTRAmEgoKBkRJWV9WMRAnEhUKEU5SRjUyODQwX1BDQTEwMDU5ECgSCgoGRFJfREVWECkSCwoHTTVTVEFDSxAqEg0KCUhFTFRFQ19WMxArEhEKDUhFTFRFQ19XU0xfVjMQLBITCg9CRVRBRlBWXzI0MDBfVFgQLRIXChNCRVRBRlBWXzkwMF9OQU5PX1RYEC4SDAoIUlBJX1BJQ08QLxIbChdIRUxURUNfV0lSRUxFU1NfVFJBQ0tFUhAwEhkKFUhFTFRFQ19XSVJFTEVTU19QQVBFUhAxEgoKBlRfREVDSxAyEg4KClRfV0FUQ0hfUzMQMxIRCg1QSUNPTVBVVEVSX1MzEDQSDwoLSEVMVEVDX0hUNjIQNRISCg5FQllURV9FU1AzMl9TMxA2EhEKDUVTUDMyX1MzX1BJQ08QNxINCglDSEFUVEVSXzIQOBIeChpIRUxURUNfV0lSRUxFU1NfUEFQRVJfVjFfMBA5EiAKHEhFTFRFQ19XSVJFTEVTU19UUkFDS0VSX1YxXzAQOhILCgdVTlBIT05FEDsSDAoIVERfTE9SQUMQPBITCg9DREVCWVRFX0VPUkFfUzMQPRIPCgtUV0NfTUVTSF9WNBA+EhYKEk5SRjUyX1BST01JQ1JPX0RJWRA/Eh8KG1JBRElPTUFTVEVSXzkwMF9CQU5ESVRfTkFOTxBAEhwKGEhFTFRFQ19DQVBTVUxFX1NFTlNPUl9WMxBBEh0KGUhFTFRFQ19WSVNJT05fTUFTVEVSX1QxOTAQQhIdChlIRUxURUNfVklTSU9OX01BU1RFUl9FMjEzEEMSHQoZSEVMVEVDX1ZJU0lPTl9NQVNURVJfRTI5MBBEEhkKFUhFTFRFQ19NRVNIX05PREVfVDExNBBFEhYKElNFTlNFQ0FQX0lORElDQVRPUhBGEhMKD1RSQUNLRVJfVDEwMDBfRRBHEgsKB1JBSzMxNzIQSBIKCgZXSU9fRTUQSRIaChZSQURJT01BU1RFUl85MDBfQkFORElUEEoSEwoPTUUyNUxTMDFfNFkxMFREEEsSGAoUUlAyMDQwX0ZFQVRIRVJfUkZNOTUQTBIVChFNNVNUQUNLX0NPUkVCQVNJQxBNEhEKDU01U1RBQ0tfQ09SRTIQThINCglSUElfUElDTzIQTxISCg5NNVNUQUNLX0NPUkVTMxBQEhEKDVNFRUVEX1hJQU9fUzMQURILCgdNUzI0U0YxEFISDAoIVExPUkFfQzYQUxIPCgtXSVNNRVNIX1RBUBBUEg0KCVJPVVRBU1RJQxBVEgwKCE1FU0hfVEFCEFYSDAoITUVTSExJTksQVxISCg5YSUFPX05SRjUyX0tJVBBYEhAKDFRISU5LTk9ERV9NMRBZEhAKDFRISU5LTk9ERV9NMhBaEg8KC1RfRVRIX0VMSVRFEFsSFQoRSEVMVEVDX1NFTlNPUl9IVUIQXBIaChZSRVNFUlZFRF9GUklFRF9DSElDS0VOEF0SFgoSSEVMVEVDX01FU0hfUE9DS0VUEF4SFAoQU0VFRURfU09MQVJfTk9ERRBfEhgKFE5PTUFEU1RBUl9NRVRFT1JfUFJPEGASDQoJQ1JPV1BBTkVMEGESCwoHTElOS18zMhBiEhgKFFNFRUVEX1dJT19UUkFDS0VSX0wxEGMSHQoZU0VFRURfV0lPX1RSQUNLRVJfTDFfRUlOSxBkEhQKEFFXQU5UWl9USU5ZX0FSTVMQZRIOCgpUX0RFQ0tfUFJPEGYSEAoMVF9MT1JBX1BBR0VSEGcSHQoZR0FUNTYyX01FU0hfVFJJQUxfVFJBQ0tFUhBoEg8KClBSSVZBVEVfSFcQ/wEqLAoJQ29uc3RhbnRzEggKBFpFUk8QABIVChBEQVRBX1BBWUxPQURfTEVOEOkBKrQCChFDcml0aWNhbEVycm9yQ29kZRIICgROT05FEAASDwoLVFhfV0FUQ0hET0cQARIUChBTTEVFUF9FTlRFUl9XQUlUEAISDAoITk9fUkFESU8QAxIPCgtVTlNQRUNJRklFRBAEEhUKEVVCTE9YX1VOSVRfRkFJTEVEEAUSDQoJTk9fQVhQMTkyEAYSGQoVSU5WQUxJRF9SQURJT19TRVRUSU5HEAcSEwoPVFJBTlNNSVRfRkFJTEVEEAgSDAoIQlJPV05PVVQQCRISCg5TWDEyNjJfRkFJTFVSRRAKEhEKDVJBRElPX1NQSV9CVUcQCxIgChxGTEFTSF9DT1JSVVBUSU9OX1JFQ09WRVJBQkxFEAwSIgoeRkxBU0hfQ09SUlVQVElPTl9VTlJFQ09WRVJBQkxFEA0qgAMKD0V4Y2x1ZGVkTW9kdWxlcxIRCg1FWENMVURFRF9OT05FEAASDwoLTVFUVF9DT05GSUcQARIRCg1TRVJJQUxfQ09ORklHEAISEwoPRVhUTk9USUZfQ09ORklHEAQSFwoTU1RPUkVGT1JXQVJEX0NPTkZJRxAIEhQKEFJBTkdFVEVTVF9DT05GSUcQEBIUChBURUxFTUVUUllfQ09ORklHECASFAoQQ0FOTkVETVNHX0NPTkZJRxBAEhEKDEFVRElPX0NPTkZJRxCAARIaChVSRU1PVEVIQVJEV0FSRV9DT05GSUcQgAISGAoTTkVJR0hCT1JJTkZPX0NPTkZJRxCABBIbChZBTUJJRU5UTElHSFRJTkdfQ09ORklHEIAIEhsKFkRFVEVDVElPTlNFTlNPUl9DT05GSUcQgBASFgoRUEFYQ09VTlRFUl9DT05GSUcQgCASFQoQQkxVRVRPT1RIX0NPTkZJRxCAQBIUCg5ORVRXT1JLX0NPTkZJRxCAgAFCXwoTY29tLmdlZWtzdmlsbGUubWVzaEIKTWVzaFByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [
	k,
	A,
	M,
	ua,
	P,
	Ta,
	zr
]), ka = /* @__PURE__ */ D(L, 0), Aa = /* @__PURE__ */ function(e) {
	return e[e.LOC_UNSET = 0] = "LOC_UNSET", e[e.LOC_MANUAL = 1] = "LOC_MANUAL", e[e.LOC_INTERNAL = 2] = "LOC_INTERNAL", e[e.LOC_EXTERNAL = 3] = "LOC_EXTERNAL", e;
}({}), ja = /* @__PURE__ */ w(L, 0, 0), Ma = /* @__PURE__ */ function(e) {
	return e[e.ALT_UNSET = 0] = "ALT_UNSET", e[e.ALT_MANUAL = 1] = "ALT_MANUAL", e[e.ALT_INTERNAL = 2] = "ALT_INTERNAL", e[e.ALT_EXTERNAL = 3] = "ALT_EXTERNAL", e[e.ALT_BAROMETRIC = 4] = "ALT_BAROMETRIC", e;
}({}), Na = /* @__PURE__ */ w(L, 0, 1), Pa = /* @__PURE__ */ D(L, 1), Fa = /* @__PURE__ */ D(L, 2), Ia = /* @__PURE__ */ D(L, 3), La = /* @__PURE__ */ function(e) {
	return e[e.NONE = 0] = "NONE", e[e.NO_ROUTE = 1] = "NO_ROUTE", e[e.GOT_NAK = 2] = "GOT_NAK", e[e.TIMEOUT = 3] = "TIMEOUT", e[e.NO_INTERFACE = 4] = "NO_INTERFACE", e[e.MAX_RETRANSMIT = 5] = "MAX_RETRANSMIT", e[e.NO_CHANNEL = 6] = "NO_CHANNEL", e[e.TOO_LARGE = 7] = "TOO_LARGE", e[e.NO_RESPONSE = 8] = "NO_RESPONSE", e[e.DUTY_CYCLE_LIMIT = 9] = "DUTY_CYCLE_LIMIT", e[e.BAD_REQUEST = 32] = "BAD_REQUEST", e[e.NOT_AUTHORIZED = 33] = "NOT_AUTHORIZED", e[e.PKI_FAILED = 34] = "PKI_FAILED", e[e.PKI_UNKNOWN_PUBKEY = 35] = "PKI_UNKNOWN_PUBKEY", e[e.ADMIN_BAD_SESSION_KEY = 36] = "ADMIN_BAD_SESSION_KEY", e[e.ADMIN_PUBLIC_KEY_UNAUTHORIZED = 37] = "ADMIN_PUBLIC_KEY_UNAUTHORIZED", e;
}({}), Ra = /* @__PURE__ */ w(L, 3, 0), za = /* @__PURE__ */ D(L, 4), Ba = /* @__PURE__ */ D(L, 5), Va = /* @__PURE__ */ D(L, 6), Ha = /* @__PURE__ */ D(L, 7), Ua = /* @__PURE__ */ D(L, 8), Wa = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.MIN = 1] = "MIN", e[e.BACKGROUND = 10] = "BACKGROUND", e[e.DEFAULT = 64] = "DEFAULT", e[e.RELIABLE = 70] = "RELIABLE", e[e.RESPONSE = 80] = "RESPONSE", e[e.HIGH = 100] = "HIGH", e[e.ALERT = 110] = "ALERT", e[e.ACK = 120] = "ACK", e[e.MAX = 127] = "MAX", e;
}({}), Ga = /* @__PURE__ */ w(L, 8, 0), Ka = /* @__PURE__ */ function(e) {
	return e[e.NO_DELAY = 0] = "NO_DELAY", e[e.DELAYED_BROADCAST = 1] = "DELAYED_BROADCAST", e[e.DELAYED_DIRECT = 2] = "DELAYED_DIRECT", e;
}({}), qa = /* @__PURE__ */ w(L, 8, 1), Ja = /* @__PURE__ */ D(L, 9), Ya = /* @__PURE__ */ D(L, 10), Xa = /* @__PURE__ */ D(L, 11), Za = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.CRITICAL = 50] = "CRITICAL", e[e.ERROR = 40] = "ERROR", e[e.WARNING = 30] = "WARNING", e[e.INFO = 20] = "INFO", e[e.DEBUG = 10] = "DEBUG", e[e.TRACE = 5] = "TRACE", e;
}({}), Qa = /* @__PURE__ */ w(L, 11, 0), $a = /* @__PURE__ */ D(L, 12), eo = /* @__PURE__ */ D(L, 13), to = /* @__PURE__ */ D(L, 14), no = /* @__PURE__ */ D(L, 15), ro = /* @__PURE__ */ D(L, 16), io = /* @__PURE__ */ D(L, 17), ao = /* @__PURE__ */ D(L, 18), oo = /* @__PURE__ */ D(L, 19), so = /* @__PURE__ */ D(L, 20), co = /* @__PURE__ */ D(L, 21), lo = /* @__PURE__ */ D(L, 22), uo = /* @__PURE__ */ D(L, 23), fo = /* @__PURE__ */ D(L, 24), po = /* @__PURE__ */ D(L, 25), mo = /* @__PURE__ */ D(L, 26), ho = /* @__PURE__ */ D(L, 27), go = /* @__PURE__ */ D(L, 28), _o = /* @__PURE__ */ D(L, 29), vo = /* @__PURE__ */ D(L, 30), yo = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.TLORA_V2 = 1] = "TLORA_V2", e[e.TLORA_V1 = 2] = "TLORA_V1", e[e.TLORA_V2_1_1P6 = 3] = "TLORA_V2_1_1P6", e[e.TBEAM = 4] = "TBEAM", e[e.HELTEC_V2_0 = 5] = "HELTEC_V2_0", e[e.TBEAM_V0P7 = 6] = "TBEAM_V0P7", e[e.T_ECHO = 7] = "T_ECHO", e[e.TLORA_V1_1P3 = 8] = "TLORA_V1_1P3", e[e.RAK4631 = 9] = "RAK4631", e[e.HELTEC_V2_1 = 10] = "HELTEC_V2_1", e[e.HELTEC_V1 = 11] = "HELTEC_V1", e[e.LILYGO_TBEAM_S3_CORE = 12] = "LILYGO_TBEAM_S3_CORE", e[e.RAK11200 = 13] = "RAK11200", e[e.NANO_G1 = 14] = "NANO_G1", e[e.TLORA_V2_1_1P8 = 15] = "TLORA_V2_1_1P8", e[e.TLORA_T3_S3 = 16] = "TLORA_T3_S3", e[e.NANO_G1_EXPLORER = 17] = "NANO_G1_EXPLORER", e[e.NANO_G2_ULTRA = 18] = "NANO_G2_ULTRA", e[e.LORA_TYPE = 19] = "LORA_TYPE", e[e.WIPHONE = 20] = "WIPHONE", e[e.WIO_WM1110 = 21] = "WIO_WM1110", e[e.RAK2560 = 22] = "RAK2560", e[e.HELTEC_HRU_3601 = 23] = "HELTEC_HRU_3601", e[e.HELTEC_WIRELESS_BRIDGE = 24] = "HELTEC_WIRELESS_BRIDGE", e[e.STATION_G1 = 25] = "STATION_G1", e[e.RAK11310 = 26] = "RAK11310", e[e.SENSELORA_RP2040 = 27] = "SENSELORA_RP2040", e[e.SENSELORA_S3 = 28] = "SENSELORA_S3", e[e.CANARYONE = 29] = "CANARYONE", e[e.RP2040_LORA = 30] = "RP2040_LORA", e[e.STATION_G2 = 31] = "STATION_G2", e[e.LORA_RELAY_V1 = 32] = "LORA_RELAY_V1", e[e.NRF52840DK = 33] = "NRF52840DK", e[e.PPR = 34] = "PPR", e[e.GENIEBLOCKS = 35] = "GENIEBLOCKS", e[e.NRF52_UNKNOWN = 36] = "NRF52_UNKNOWN", e[e.PORTDUINO = 37] = "PORTDUINO", e[e.ANDROID_SIM = 38] = "ANDROID_SIM", e[e.DIY_V1 = 39] = "DIY_V1", e[e.NRF52840_PCA10059 = 40] = "NRF52840_PCA10059", e[e.DR_DEV = 41] = "DR_DEV", e[e.M5STACK = 42] = "M5STACK", e[e.HELTEC_V3 = 43] = "HELTEC_V3", e[e.HELTEC_WSL_V3 = 44] = "HELTEC_WSL_V3", e[e.BETAFPV_2400_TX = 45] = "BETAFPV_2400_TX", e[e.BETAFPV_900_NANO_TX = 46] = "BETAFPV_900_NANO_TX", e[e.RPI_PICO = 47] = "RPI_PICO", e[e.HELTEC_WIRELESS_TRACKER = 48] = "HELTEC_WIRELESS_TRACKER", e[e.HELTEC_WIRELESS_PAPER = 49] = "HELTEC_WIRELESS_PAPER", e[e.T_DECK = 50] = "T_DECK", e[e.T_WATCH_S3 = 51] = "T_WATCH_S3", e[e.PICOMPUTER_S3 = 52] = "PICOMPUTER_S3", e[e.HELTEC_HT62 = 53] = "HELTEC_HT62", e[e.EBYTE_ESP32_S3 = 54] = "EBYTE_ESP32_S3", e[e.ESP32_S3_PICO = 55] = "ESP32_S3_PICO", e[e.CHATTER_2 = 56] = "CHATTER_2", e[e.HELTEC_WIRELESS_PAPER_V1_0 = 57] = "HELTEC_WIRELESS_PAPER_V1_0", e[e.HELTEC_WIRELESS_TRACKER_V1_0 = 58] = "HELTEC_WIRELESS_TRACKER_V1_0", e[e.UNPHONE = 59] = "UNPHONE", e[e.TD_LORAC = 60] = "TD_LORAC", e[e.CDEBYTE_EORA_S3 = 61] = "CDEBYTE_EORA_S3", e[e.TWC_MESH_V4 = 62] = "TWC_MESH_V4", e[e.NRF52_PROMICRO_DIY = 63] = "NRF52_PROMICRO_DIY", e[e.RADIOMASTER_900_BANDIT_NANO = 64] = "RADIOMASTER_900_BANDIT_NANO", e[e.HELTEC_CAPSULE_SENSOR_V3 = 65] = "HELTEC_CAPSULE_SENSOR_V3", e[e.HELTEC_VISION_MASTER_T190 = 66] = "HELTEC_VISION_MASTER_T190", e[e.HELTEC_VISION_MASTER_E213 = 67] = "HELTEC_VISION_MASTER_E213", e[e.HELTEC_VISION_MASTER_E290 = 68] = "HELTEC_VISION_MASTER_E290", e[e.HELTEC_MESH_NODE_T114 = 69] = "HELTEC_MESH_NODE_T114", e[e.SENSECAP_INDICATOR = 70] = "SENSECAP_INDICATOR", e[e.TRACKER_T1000_E = 71] = "TRACKER_T1000_E", e[e.RAK3172 = 72] = "RAK3172", e[e.WIO_E5 = 73] = "WIO_E5", e[e.RADIOMASTER_900_BANDIT = 74] = "RADIOMASTER_900_BANDIT", e[e.ME25LS01_4Y10TD = 75] = "ME25LS01_4Y10TD", e[e.RP2040_FEATHER_RFM95 = 76] = "RP2040_FEATHER_RFM95", e[e.M5STACK_COREBASIC = 77] = "M5STACK_COREBASIC", e[e.M5STACK_CORE2 = 78] = "M5STACK_CORE2", e[e.RPI_PICO2 = 79] = "RPI_PICO2", e[e.M5STACK_CORES3 = 80] = "M5STACK_CORES3", e[e.SEEED_XIAO_S3 = 81] = "SEEED_XIAO_S3", e[e.MS24SF1 = 82] = "MS24SF1", e[e.TLORA_C6 = 83] = "TLORA_C6", e[e.WISMESH_TAP = 84] = "WISMESH_TAP", e[e.ROUTASTIC = 85] = "ROUTASTIC", e[e.MESH_TAB = 86] = "MESH_TAB", e[e.MESHLINK = 87] = "MESHLINK", e[e.XIAO_NRF52_KIT = 88] = "XIAO_NRF52_KIT", e[e.THINKNODE_M1 = 89] = "THINKNODE_M1", e[e.THINKNODE_M2 = 90] = "THINKNODE_M2", e[e.T_ETH_ELITE = 91] = "T_ETH_ELITE", e[e.HELTEC_SENSOR_HUB = 92] = "HELTEC_SENSOR_HUB", e[e.RESERVED_FRIED_CHICKEN = 93] = "RESERVED_FRIED_CHICKEN", e[e.HELTEC_MESH_POCKET = 94] = "HELTEC_MESH_POCKET", e[e.SEEED_SOLAR_NODE = 95] = "SEEED_SOLAR_NODE", e[e.NOMADSTAR_METEOR_PRO = 96] = "NOMADSTAR_METEOR_PRO", e[e.CROWPANEL = 97] = "CROWPANEL", e[e.LINK_32 = 98] = "LINK_32", e[e.SEEED_WIO_TRACKER_L1 = 99] = "SEEED_WIO_TRACKER_L1", e[e.SEEED_WIO_TRACKER_L1_EINK = 100] = "SEEED_WIO_TRACKER_L1_EINK", e[e.QWANTZ_TINY_ARMS = 101] = "QWANTZ_TINY_ARMS", e[e.T_DECK_PRO = 102] = "T_DECK_PRO", e[e.T_LORA_PAGER = 103] = "T_LORA_PAGER", e[e.GAT562_MESH_TRIAL_TRACKER = 104] = "GAT562_MESH_TRIAL_TRACKER", e[e.PRIVATE_HW = 255] = "PRIVATE_HW", e;
}({}), bo = /* @__PURE__ */ w(L, 0), xo = /* @__PURE__ */ function(e) {
	return e[e.ZERO = 0] = "ZERO", e[e.DATA_PAYLOAD_LEN = 233] = "DATA_PAYLOAD_LEN", e;
}({}), So = /* @__PURE__ */ w(L, 1), Co = /* @__PURE__ */ function(e) {
	return e[e.NONE = 0] = "NONE", e[e.TX_WATCHDOG = 1] = "TX_WATCHDOG", e[e.SLEEP_ENTER_WAIT = 2] = "SLEEP_ENTER_WAIT", e[e.NO_RADIO = 3] = "NO_RADIO", e[e.UNSPECIFIED = 4] = "UNSPECIFIED", e[e.UBLOX_UNIT_FAILED = 5] = "UBLOX_UNIT_FAILED", e[e.NO_AXP192 = 6] = "NO_AXP192", e[e.INVALID_RADIO_SETTING = 7] = "INVALID_RADIO_SETTING", e[e.TRANSMIT_FAILED = 8] = "TRANSMIT_FAILED", e[e.BROWNOUT = 9] = "BROWNOUT", e[e.SX1262_FAILURE = 10] = "SX1262_FAILURE", e[e.RADIO_SPI_BUG = 11] = "RADIO_SPI_BUG", e[e.FLASH_CORRUPTION_RECOVERABLE = 12] = "FLASH_CORRUPTION_RECOVERABLE", e[e.FLASH_CORRUPTION_UNRECOVERABLE = 13] = "FLASH_CORRUPTION_UNRECOVERABLE", e;
}({}), wo = /* @__PURE__ */ w(L, 2), To = /* @__PURE__ */ function(e) {
	return e[e.EXCLUDED_NONE = 0] = "EXCLUDED_NONE", e[e.MQTT_CONFIG = 1] = "MQTT_CONFIG", e[e.SERIAL_CONFIG = 2] = "SERIAL_CONFIG", e[e.EXTNOTIF_CONFIG = 4] = "EXTNOTIF_CONFIG", e[e.STOREFORWARD_CONFIG = 8] = "STOREFORWARD_CONFIG", e[e.RANGETEST_CONFIG = 16] = "RANGETEST_CONFIG", e[e.TELEMETRY_CONFIG = 32] = "TELEMETRY_CONFIG", e[e.CANNEDMSG_CONFIG = 64] = "CANNEDMSG_CONFIG", e[e.AUDIO_CONFIG = 128] = "AUDIO_CONFIG", e[e.REMOTEHARDWARE_CONFIG = 256] = "REMOTEHARDWARE_CONFIG", e[e.NEIGHBORINFO_CONFIG = 512] = "NEIGHBORINFO_CONFIG", e[e.AMBIENTLIGHTING_CONFIG = 1024] = "AMBIENTLIGHTING_CONFIG", e[e.DETECTIONSENSOR_CONFIG = 2048] = "DETECTIONSENSOR_CONFIG", e[e.PAXCOUNTER_CONFIG = 4096] = "PAXCOUNTER_CONFIG", e[e.BLUETOOTH_CONFIG = 8192] = "BLUETOOTH_CONFIG", e[e.NETWORK_CONFIG = 16384] = "NETWORK_CONFIG", e;
}({}), Eo = /* @__PURE__ */ w(L, 3), R = m({
	AdminMessageSchema: () => Do,
	AdminMessage_BackupLocation: () => No,
	AdminMessage_BackupLocationSchema: () => Po,
	AdminMessage_ConfigType: () => ko,
	AdminMessage_ConfigTypeSchema: () => Ao,
	AdminMessage_InputEventSchema: () => Oo,
	AdminMessage_ModuleConfigType: () => jo,
	AdminMessage_ModuleConfigTypeSchema: () => Mo,
	HamParametersSchema: () => Fo,
	KeyVerificationAdminSchema: () => Ro,
	KeyVerificationAdmin_MessageType: () => zo,
	KeyVerificationAdmin_MessageTypeSchema: () => Bo,
	NodeRemoteHardwarePinsResponseSchema: () => Io,
	SharedContactSchema: () => Lo,
	file_admin: () => z
}), z = /* @__PURE__ */ E("CgthZG1pbi5wcm90bxIKbWVzaHRhc3RpYyLWGAoMQWRtaW5NZXNzYWdlEhcKD3Nlc3Npb25fcGFzc2tleRhlIAEoDBIdChNnZXRfY2hhbm5lbF9yZXF1ZXN0GAEgASgNSAASMwoUZ2V0X2NoYW5uZWxfcmVzcG9uc2UYAiABKAsyEy5tZXNodGFzdGljLkNoYW5uZWxIABIbChFnZXRfb3duZXJfcmVxdWVzdBgDIAEoCEgAEi4KEmdldF9vd25lcl9yZXNwb25zZRgEIAEoCzIQLm1lc2h0YXN0aWMuVXNlckgAEkEKEmdldF9jb25maWdfcmVxdWVzdBgFIAEoDjIjLm1lc2h0YXN0aWMuQWRtaW5NZXNzYWdlLkNvbmZpZ1R5cGVIABIxChNnZXRfY29uZmlnX3Jlc3BvbnNlGAYgASgLMhIubWVzaHRhc3RpYy5Db25maWdIABJOChlnZXRfbW9kdWxlX2NvbmZpZ19yZXF1ZXN0GAcgASgOMikubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuTW9kdWxlQ29uZmlnVHlwZUgAEj4KGmdldF9tb2R1bGVfY29uZmlnX3Jlc3BvbnNlGAggASgLMhgubWVzaHRhc3RpYy5Nb2R1bGVDb25maWdIABI0CipnZXRfY2FubmVkX21lc3NhZ2VfbW9kdWxlX21lc3NhZ2VzX3JlcXVlc3QYCiABKAhIABI1CitnZXRfY2FubmVkX21lc3NhZ2VfbW9kdWxlX21lc3NhZ2VzX3Jlc3BvbnNlGAsgASgJSAASJQobZ2V0X2RldmljZV9tZXRhZGF0YV9yZXF1ZXN0GAwgASgISAASQgocZ2V0X2RldmljZV9tZXRhZGF0YV9yZXNwb25zZRgNIAEoCzIaLm1lc2h0YXN0aWMuRGV2aWNlTWV0YWRhdGFIABIeChRnZXRfcmluZ3RvbmVfcmVxdWVzdBgOIAEoCEgAEh8KFWdldF9yaW5ndG9uZV9yZXNwb25zZRgPIAEoCUgAEi4KJGdldF9kZXZpY2VfY29ubmVjdGlvbl9zdGF0dXNfcmVxdWVzdBgQIAEoCEgAElMKJWdldF9kZXZpY2VfY29ubmVjdGlvbl9zdGF0dXNfcmVzcG9uc2UYESABKAsyIi5tZXNodGFzdGljLkRldmljZUNvbm5lY3Rpb25TdGF0dXNIABIxCgxzZXRfaGFtX21vZGUYEiABKAsyGS5tZXNodGFzdGljLkhhbVBhcmFtZXRlcnNIABIvCiVnZXRfbm9kZV9yZW1vdGVfaGFyZHdhcmVfcGluc19yZXF1ZXN0GBMgASgISAASXAomZ2V0X25vZGVfcmVtb3RlX2hhcmR3YXJlX3BpbnNfcmVzcG9uc2UYFCABKAsyKi5tZXNodGFzdGljLk5vZGVSZW1vdGVIYXJkd2FyZVBpbnNSZXNwb25zZUgAEiAKFmVudGVyX2RmdV9tb2RlX3JlcXVlc3QYFSABKAhIABIdChNkZWxldGVfZmlsZV9yZXF1ZXN0GBYgASgJSAASEwoJc2V0X3NjYWxlGBcgASgNSAASRQoSYmFja3VwX3ByZWZlcmVuY2VzGBggASgOMicubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuQmFja3VwTG9jYXRpb25IABJGChNyZXN0b3JlX3ByZWZlcmVuY2VzGBkgASgOMicubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuQmFja3VwTG9jYXRpb25IABJMChlyZW1vdmVfYmFja3VwX3ByZWZlcmVuY2VzGBogASgOMicubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuQmFja3VwTG9jYXRpb25IABI/ChBzZW5kX2lucHV0X2V2ZW50GBsgASgLMiMubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuSW5wdXRFdmVudEgAEiUKCXNldF9vd25lchggIAEoCzIQLm1lc2h0YXN0aWMuVXNlckgAEioKC3NldF9jaGFubmVsGCEgASgLMhMubWVzaHRhc3RpYy5DaGFubmVsSAASKAoKc2V0X2NvbmZpZxgiIAEoCzISLm1lc2h0YXN0aWMuQ29uZmlnSAASNQoRc2V0X21vZHVsZV9jb25maWcYIyABKAsyGC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZ0gAEiwKInNldF9jYW5uZWRfbWVzc2FnZV9tb2R1bGVfbWVzc2FnZXMYJCABKAlIABIeChRzZXRfcmluZ3RvbmVfbWVzc2FnZRglIAEoCUgAEhsKEXJlbW92ZV9ieV9ub2RlbnVtGCYgASgNSAASGwoRc2V0X2Zhdm9yaXRlX25vZGUYJyABKA1IABIeChRyZW1vdmVfZmF2b3JpdGVfbm9kZRgoIAEoDUgAEjIKEnNldF9maXhlZF9wb3NpdGlvbhgpIAEoCzIULm1lc2h0YXN0aWMuUG9zaXRpb25IABIfChVyZW1vdmVfZml4ZWRfcG9zaXRpb24YKiABKAhIABIXCg1zZXRfdGltZV9vbmx5GCsgASgHSAASHwoVZ2V0X3VpX2NvbmZpZ19yZXF1ZXN0GCwgASgISAASPAoWZ2V0X3VpX2NvbmZpZ19yZXNwb25zZRgtIAEoCzIaLm1lc2h0YXN0aWMuRGV2aWNlVUlDb25maWdIABI1Cg9zdG9yZV91aV9jb25maWcYLiABKAsyGi5tZXNodGFzdGljLkRldmljZVVJQ29uZmlnSAASGgoQc2V0X2lnbm9yZWRfbm9kZRgvIAEoDUgAEh0KE3JlbW92ZV9pZ25vcmVkX25vZGUYMCABKA1IABIdChNiZWdpbl9lZGl0X3NldHRpbmdzGEAgASgISAASHgoUY29tbWl0X2VkaXRfc2V0dGluZ3MYQSABKAhIABIwCgthZGRfY29udGFjdBhCIAEoCzIZLm1lc2h0YXN0aWMuU2hhcmVkQ29udGFjdEgAEjwKEGtleV92ZXJpZmljYXRpb24YQyABKAsyIC5tZXNodGFzdGljLktleVZlcmlmaWNhdGlvbkFkbWluSAASHgoUZmFjdG9yeV9yZXNldF9kZXZpY2UYXiABKAVIABIcChJyZWJvb3Rfb3RhX3NlY29uZHMYXyABKAVIABIYCg5leGl0X3NpbXVsYXRvchhgIAEoCEgAEhgKDnJlYm9vdF9zZWNvbmRzGGEgASgFSAASGgoQc2h1dGRvd25fc2Vjb25kcxhiIAEoBUgAEh4KFGZhY3RvcnlfcmVzZXRfY29uZmlnGGMgASgFSAASFgoMbm9kZWRiX3Jlc2V0GGQgASgFSAAaUwoKSW5wdXRFdmVudBISCgpldmVudF9jb2RlGAEgASgNEg8KB2tiX2NoYXIYAiABKA0SDwoHdG91Y2hfeBgDIAEoDRIPCgd0b3VjaF95GAQgASgNItYBCgpDb25maWdUeXBlEhEKDURFVklDRV9DT05GSUcQABITCg9QT1NJVElPTl9DT05GSUcQARIQCgxQT1dFUl9DT05GSUcQAhISCg5ORVRXT1JLX0NPTkZJRxADEhIKDkRJU1BMQVlfQ09ORklHEAQSDwoLTE9SQV9DT05GSUcQBRIUChBCTFVFVE9PVEhfQ09ORklHEAYSEwoPU0VDVVJJVFlfQ09ORklHEAcSFQoRU0VTU0lPTktFWV9DT05GSUcQCBITCg9ERVZJQ0VVSV9DT05GSUcQCSK7AgoQTW9kdWxlQ29uZmlnVHlwZRIPCgtNUVRUX0NPTkZJRxAAEhEKDVNFUklBTF9DT05GSUcQARITCg9FWFROT1RJRl9DT05GSUcQAhIXChNTVE9SRUZPUldBUkRfQ09ORklHEAMSFAoQUkFOR0VURVNUX0NPTkZJRxAEEhQKEFRFTEVNRVRSWV9DT05GSUcQBRIUChBDQU5ORURNU0dfQ09ORklHEAYSEAoMQVVESU9fQ09ORklHEAcSGQoVUkVNT1RFSEFSRFdBUkVfQ09ORklHEAgSFwoTTkVJR0hCT1JJTkZPX0NPTkZJRxAJEhoKFkFNQklFTlRMSUdIVElOR19DT05GSUcQChIaChZERVRFQ1RJT05TRU5TT1JfQ09ORklHEAsSFQoRUEFYQ09VTlRFUl9DT05GSUcQDCIjCg5CYWNrdXBMb2NhdGlvbhIJCgVGTEFTSBAAEgYKAlNEEAFCEQoPcGF5bG9hZF92YXJpYW50IlsKDUhhbVBhcmFtZXRlcnMSEQoJY2FsbF9zaWduGAEgASgJEhAKCHR4X3Bvd2VyGAIgASgFEhEKCWZyZXF1ZW5jeRgDIAEoAhISCgpzaG9ydF9uYW1lGAQgASgJImYKHk5vZGVSZW1vdGVIYXJkd2FyZVBpbnNSZXNwb25zZRJEChlub2RlX3JlbW90ZV9oYXJkd2FyZV9waW5zGAEgAygLMiEubWVzaHRhc3RpYy5Ob2RlUmVtb3RlSGFyZHdhcmVQaW4iWAoNU2hhcmVkQ29udGFjdBIQCghub2RlX251bRgBIAEoDRIeCgR1c2VyGAIgASgLMhAubWVzaHRhc3RpYy5Vc2VyEhUKDXNob3VsZF9pZ25vcmUYAyABKAginAIKFEtleVZlcmlmaWNhdGlvbkFkbWluEkIKDG1lc3NhZ2VfdHlwZRgBIAEoDjIsLm1lc2h0YXN0aWMuS2V5VmVyaWZpY2F0aW9uQWRtaW4uTWVzc2FnZVR5cGUSFgoOcmVtb3RlX25vZGVudW0YAiABKA0SDQoFbm9uY2UYAyABKAQSHAoPc2VjdXJpdHlfbnVtYmVyGAQgASgNSACIAQEiZwoLTWVzc2FnZVR5cGUSGQoVSU5JVElBVEVfVkVSSUZJQ0FUSU9OEAASGwoXUFJPVklERV9TRUNVUklUWV9OVU1CRVIQARINCglET19WRVJJRlkQAhIRCg1ET19OT1RfVkVSSUZZEANCEgoQX3NlY3VyaXR5X251bWJlckJgChNjb20uZ2Vla3N2aWxsZS5tZXNoQgtBZG1pblByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [
	k,
	A,
	j,
	L,
	M,
	zr
]), Do = /* @__PURE__ */ D(z, 0), Oo = /* @__PURE__ */ D(z, 0, 0), ko = /* @__PURE__ */ function(e) {
	return e[e.DEVICE_CONFIG = 0] = "DEVICE_CONFIG", e[e.POSITION_CONFIG = 1] = "POSITION_CONFIG", e[e.POWER_CONFIG = 2] = "POWER_CONFIG", e[e.NETWORK_CONFIG = 3] = "NETWORK_CONFIG", e[e.DISPLAY_CONFIG = 4] = "DISPLAY_CONFIG", e[e.LORA_CONFIG = 5] = "LORA_CONFIG", e[e.BLUETOOTH_CONFIG = 6] = "BLUETOOTH_CONFIG", e[e.SECURITY_CONFIG = 7] = "SECURITY_CONFIG", e[e.SESSIONKEY_CONFIG = 8] = "SESSIONKEY_CONFIG", e[e.DEVICEUI_CONFIG = 9] = "DEVICEUI_CONFIG", e;
}({}), Ao = /* @__PURE__ */ w(z, 0, 0), jo = /* @__PURE__ */ function(e) {
	return e[e.MQTT_CONFIG = 0] = "MQTT_CONFIG", e[e.SERIAL_CONFIG = 1] = "SERIAL_CONFIG", e[e.EXTNOTIF_CONFIG = 2] = "EXTNOTIF_CONFIG", e[e.STOREFORWARD_CONFIG = 3] = "STOREFORWARD_CONFIG", e[e.RANGETEST_CONFIG = 4] = "RANGETEST_CONFIG", e[e.TELEMETRY_CONFIG = 5] = "TELEMETRY_CONFIG", e[e.CANNEDMSG_CONFIG = 6] = "CANNEDMSG_CONFIG", e[e.AUDIO_CONFIG = 7] = "AUDIO_CONFIG", e[e.REMOTEHARDWARE_CONFIG = 8] = "REMOTEHARDWARE_CONFIG", e[e.NEIGHBORINFO_CONFIG = 9] = "NEIGHBORINFO_CONFIG", e[e.AMBIENTLIGHTING_CONFIG = 10] = "AMBIENTLIGHTING_CONFIG", e[e.DETECTIONSENSOR_CONFIG = 11] = "DETECTIONSENSOR_CONFIG", e[e.PAXCOUNTER_CONFIG = 12] = "PAXCOUNTER_CONFIG", e;
}({}), Mo = /* @__PURE__ */ w(z, 0, 1), No = /* @__PURE__ */ function(e) {
	return e[e.FLASH = 0] = "FLASH", e[e.SD = 1] = "SD", e;
}({}), Po = /* @__PURE__ */ w(z, 0, 2), Fo = /* @__PURE__ */ D(z, 1), Io = /* @__PURE__ */ D(z, 2), Lo = /* @__PURE__ */ D(z, 3), Ro = /* @__PURE__ */ D(z, 4), zo = /* @__PURE__ */ function(e) {
	return e[e.INITIATE_VERIFICATION = 0] = "INITIATE_VERIFICATION", e[e.PROVIDE_SECURITY_NUMBER = 1] = "PROVIDE_SECURITY_NUMBER", e[e.DO_VERIFY = 2] = "DO_VERIFY", e[e.DO_NOT_VERIFY = 3] = "DO_NOT_VERIFY", e;
}({}), Bo = /* @__PURE__ */ w(z, 4, 0), Vo = m({
	ChannelSetSchema: () => Uo,
	file_apponly: () => Ho
}), Ho = /* @__PURE__ */ E("Cg1hcHBvbmx5LnByb3RvEgptZXNodGFzdGljIm8KCkNoYW5uZWxTZXQSLQoIc2V0dGluZ3MYASADKAsyGy5tZXNodGFzdGljLkNoYW5uZWxTZXR0aW5ncxIyCgtsb3JhX2NvbmZpZxgCIAEoCzIdLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWdCYgoTY29tLmdlZWtzdmlsbGUubWVzaEINQXBwT25seVByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [k, A]), Uo = /* @__PURE__ */ D(Ho, 0), Wo = m({
	ContactSchema: () => Yo,
	GeoChatSchema: () => Ko,
	GroupSchema: () => qo,
	MemberRole: () => $o,
	MemberRoleSchema: () => es,
	PLISchema: () => Xo,
	StatusSchema: () => Jo,
	TAKPacketSchema: () => Go,
	Team: () => Zo,
	TeamSchema: () => Qo,
	file_atak: () => B
}), B = /* @__PURE__ */ E("CgphdGFrLnByb3RvEgptZXNodGFzdGljIvgBCglUQUtQYWNrZXQSFQoNaXNfY29tcHJlc3NlZBgBIAEoCBIkCgdjb250YWN0GAIgASgLMhMubWVzaHRhc3RpYy5Db250YWN0EiAKBWdyb3VwGAMgASgLMhEubWVzaHRhc3RpYy5Hcm91cBIiCgZzdGF0dXMYBCABKAsyEi5tZXNodGFzdGljLlN0YXR1cxIeCgNwbGkYBSABKAsyDy5tZXNodGFzdGljLlBMSUgAEiMKBGNoYXQYBiABKAsyEy5tZXNodGFzdGljLkdlb0NoYXRIABIQCgZkZXRhaWwYByABKAxIAEIRCg9wYXlsb2FkX3ZhcmlhbnQiXAoHR2VvQ2hhdBIPCgdtZXNzYWdlGAEgASgJEg8KAnRvGAIgASgJSACIAQESGAoLdG9fY2FsbHNpZ24YAyABKAlIAYgBAUIFCgNfdG9CDgoMX3RvX2NhbGxzaWduIk0KBUdyb3VwEiQKBHJvbGUYASABKA4yFi5tZXNodGFzdGljLk1lbWJlclJvbGUSHgoEdGVhbRgCIAEoDjIQLm1lc2h0YXN0aWMuVGVhbSIZCgZTdGF0dXMSDwoHYmF0dGVyeRgBIAEoDSI0CgdDb250YWN0EhAKCGNhbGxzaWduGAEgASgJEhcKD2RldmljZV9jYWxsc2lnbhgCIAEoCSJfCgNQTEkSEgoKbGF0aXR1ZGVfaRgBIAEoDxITCgtsb25naXR1ZGVfaRgCIAEoDxIQCghhbHRpdHVkZRgDIAEoBRINCgVzcGVlZBgEIAEoDRIOCgZjb3Vyc2UYBSABKA0qwAEKBFRlYW0SFAoQVW5zcGVjaWZlZF9Db2xvchAAEgkKBVdoaXRlEAESCgoGWWVsbG93EAISCgoGT3JhbmdlEAMSCwoHTWFnZW50YRAEEgcKA1JlZBAFEgoKBk1hcm9vbhAGEgoKBlB1cnBsZRAHEg0KCURhcmtfQmx1ZRAIEggKBEJsdWUQCRIICgRDeWFuEAoSCAoEVGVhbBALEgkKBUdyZWVuEAwSDgoKRGFya19HcmVlbhANEgkKBUJyb3duEA4qfwoKTWVtYmVyUm9sZRIOCgpVbnNwZWNpZmVkEAASDgoKVGVhbU1lbWJlchABEgwKCFRlYW1MZWFkEAISBgoCSFEQAxIKCgZTbmlwZXIQBBIJCgVNZWRpYxAFEhMKD0ZvcndhcmRPYnNlcnZlchAGEgcKA1JUTxAHEgYKAks5EAhCXwoTY29tLmdlZWtzdmlsbGUubWVzaEIKQVRBS1Byb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM"), Go = /* @__PURE__ */ D(B, 0), Ko = /* @__PURE__ */ D(B, 1), qo = /* @__PURE__ */ D(B, 2), Jo = /* @__PURE__ */ D(B, 3), Yo = /* @__PURE__ */ D(B, 4), Xo = /* @__PURE__ */ D(B, 5), Zo = /* @__PURE__ */ function(e) {
	return e[e.Unspecifed_Color = 0] = "Unspecifed_Color", e[e.White = 1] = "White", e[e.Yellow = 2] = "Yellow", e[e.Orange = 3] = "Orange", e[e.Magenta = 4] = "Magenta", e[e.Red = 5] = "Red", e[e.Maroon = 6] = "Maroon", e[e.Purple = 7] = "Purple", e[e.Dark_Blue = 8] = "Dark_Blue", e[e.Blue = 9] = "Blue", e[e.Cyan = 10] = "Cyan", e[e.Teal = 11] = "Teal", e[e.Green = 12] = "Green", e[e.Dark_Green = 13] = "Dark_Green", e[e.Brown = 14] = "Brown", e;
}({}), Qo = /* @__PURE__ */ w(B, 0), $o = /* @__PURE__ */ function(e) {
	return e[e.Unspecifed = 0] = "Unspecifed", e[e.TeamMember = 1] = "TeamMember", e[e.TeamLead = 2] = "TeamLead", e[e.HQ = 3] = "HQ", e[e.Sniper = 4] = "Sniper", e[e.Medic = 5] = "Medic", e[e.ForwardObserver = 6] = "ForwardObserver", e[e.RTO = 7] = "RTO", e[e.K9 = 8] = "K9", e;
}({}), es = /* @__PURE__ */ w(B, 1), ts = m({
	CannedMessageModuleConfigSchema: () => rs,
	file_cannedmessages: () => ns
}), ns = /* @__PURE__ */ E("ChRjYW5uZWRtZXNzYWdlcy5wcm90bxIKbWVzaHRhc3RpYyItChlDYW5uZWRNZXNzYWdlTW9kdWxlQ29uZmlnEhAKCG1lc3NhZ2VzGAEgASgJQm4KE2NvbS5nZWVrc3ZpbGxlLm1lc2hCGUNhbm5lZE1lc3NhZ2VDb25maWdQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), rs = /* @__PURE__ */ D(ns, 0), is = m({
	LocalConfigSchema: () => os,
	LocalModuleConfigSchema: () => ss,
	file_localonly: () => as
}), as = /* @__PURE__ */ E("Cg9sb2NhbG9ubHkucHJvdG8SCm1lc2h0YXN0aWMisgMKC0xvY2FsQ29uZmlnEi8KBmRldmljZRgBIAEoCzIfLm1lc2h0YXN0aWMuQ29uZmlnLkRldmljZUNvbmZpZxIzCghwb3NpdGlvbhgCIAEoCzIhLm1lc2h0YXN0aWMuQ29uZmlnLlBvc2l0aW9uQ29uZmlnEi0KBXBvd2VyGAMgASgLMh4ubWVzaHRhc3RpYy5Db25maWcuUG93ZXJDb25maWcSMQoHbmV0d29yaxgEIAEoCzIgLm1lc2h0YXN0aWMuQ29uZmlnLk5ldHdvcmtDb25maWcSMQoHZGlzcGxheRgFIAEoCzIgLm1lc2h0YXN0aWMuQ29uZmlnLkRpc3BsYXlDb25maWcSKwoEbG9yYRgGIAEoCzIdLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWcSNQoJYmx1ZXRvb3RoGAcgASgLMiIubWVzaHRhc3RpYy5Db25maWcuQmx1ZXRvb3RoQ29uZmlnEg8KB3ZlcnNpb24YCCABKA0SMwoIc2VjdXJpdHkYCSABKAsyIS5tZXNodGFzdGljLkNvbmZpZy5TZWN1cml0eUNvbmZpZyL7BgoRTG9jYWxNb2R1bGVDb25maWcSMQoEbXF0dBgBIAEoCzIjLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk1RVFRDb25maWcSNQoGc2VyaWFsGAIgASgLMiUubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuU2VyaWFsQ29uZmlnElIKFWV4dGVybmFsX25vdGlmaWNhdGlvbhgDIAEoCzIzLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkV4dGVybmFsTm90aWZpY2F0aW9uQ29uZmlnEkIKDXN0b3JlX2ZvcndhcmQYBCABKAsyKy5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TdG9yZUZvcndhcmRDb25maWcSPAoKcmFuZ2VfdGVzdBgFIAEoCzIoLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlJhbmdlVGVzdENvbmZpZxI7Cgl0ZWxlbWV0cnkYBiABKAsyKC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5UZWxlbWV0cnlDb25maWcSRAoOY2FubmVkX21lc3NhZ2UYByABKAsyLC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5DYW5uZWRNZXNzYWdlQ29uZmlnEjMKBWF1ZGlvGAkgASgLMiQubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuQXVkaW9Db25maWcSRgoPcmVtb3RlX2hhcmR3YXJlGAogASgLMi0ubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuUmVtb3RlSGFyZHdhcmVDb25maWcSQgoNbmVpZ2hib3JfaW5mbxgLIAEoCzIrLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk5laWdoYm9ySW5mb0NvbmZpZxJIChBhbWJpZW50X2xpZ2h0aW5nGAwgASgLMi4ubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuQW1iaWVudExpZ2h0aW5nQ29uZmlnEkgKEGRldGVjdGlvbl9zZW5zb3IYDSABKAsyLi5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5EZXRlY3Rpb25TZW5zb3JDb25maWcSPQoKcGF4Y291bnRlchgOIAEoCzIpLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlBheGNvdW50ZXJDb25maWcSDwoHdmVyc2lvbhgIIAEoDUJkChNjb20uZ2Vla3N2aWxsZS5tZXNoQg9Mb2NhbE9ubHlQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z", [A, M]), os = /* @__PURE__ */ D(as, 0), ss = /* @__PURE__ */ D(as, 1), cs = m({
	DeviceProfileSchema: () => us,
	file_clientonly: () => ls
}), ls = /* @__PURE__ */ E("ChBjbGllbnRvbmx5LnByb3RvEgptZXNodGFzdGljIqkDCg1EZXZpY2VQcm9maWxlEhYKCWxvbmdfbmFtZRgBIAEoCUgAiAEBEhcKCnNob3J0X25hbWUYAiABKAlIAYgBARIYCgtjaGFubmVsX3VybBgDIAEoCUgCiAEBEiwKBmNvbmZpZxgEIAEoCzIXLm1lc2h0YXN0aWMuTG9jYWxDb25maWdIA4gBARI5Cg1tb2R1bGVfY29uZmlnGAUgASgLMh0ubWVzaHRhc3RpYy5Mb2NhbE1vZHVsZUNvbmZpZ0gEiAEBEjEKDmZpeGVkX3Bvc2l0aW9uGAYgASgLMhQubWVzaHRhc3RpYy5Qb3NpdGlvbkgFiAEBEhUKCHJpbmd0b25lGAcgASgJSAaIAQESHAoPY2FubmVkX21lc3NhZ2VzGAggASgJSAeIAQFCDAoKX2xvbmdfbmFtZUINCgtfc2hvcnRfbmFtZUIOCgxfY2hhbm5lbF91cmxCCQoHX2NvbmZpZ0IQCg5fbW9kdWxlX2NvbmZpZ0IRCg9fZml4ZWRfcG9zaXRpb25CCwoJX3Jpbmd0b25lQhIKEF9jYW5uZWRfbWVzc2FnZXNCZQoTY29tLmdlZWtzdmlsbGUubWVzaEIQQ2xpZW50T25seVByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [as, L]), us = /* @__PURE__ */ D(ls, 0), ds = m({
	MapReportSchema: () => ms,
	ServiceEnvelopeSchema: () => ps,
	file_mqtt: () => fs
}), fs = /* @__PURE__ */ E("CgptcXR0LnByb3RvEgptZXNodGFzdGljImEKD1NlcnZpY2VFbnZlbG9wZRImCgZwYWNrZXQYASABKAsyFi5tZXNodGFzdGljLk1lc2hQYWNrZXQSEgoKY2hhbm5lbF9pZBgCIAEoCRISCgpnYXRld2F5X2lkGAMgASgJIt8DCglNYXBSZXBvcnQSEQoJbG9uZ19uYW1lGAEgASgJEhIKCnNob3J0X25hbWUYAiABKAkSMgoEcm9sZRgDIAEoDjIkLm1lc2h0YXN0aWMuQ29uZmlnLkRldmljZUNvbmZpZy5Sb2xlEisKCGh3X21vZGVsGAQgASgOMhkubWVzaHRhc3RpYy5IYXJkd2FyZU1vZGVsEhgKEGZpcm13YXJlX3ZlcnNpb24YBSABKAkSOAoGcmVnaW9uGAYgASgOMigubWVzaHRhc3RpYy5Db25maWcuTG9SYUNvbmZpZy5SZWdpb25Db2RlEj8KDG1vZGVtX3ByZXNldBgHIAEoDjIpLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWcuTW9kZW1QcmVzZXQSGwoTaGFzX2RlZmF1bHRfY2hhbm5lbBgIIAEoCBISCgpsYXRpdHVkZV9pGAkgASgPEhMKC2xvbmdpdHVkZV9pGAogASgPEhAKCGFsdGl0dWRlGAsgASgFEhoKEnBvc2l0aW9uX3ByZWNpc2lvbhgMIAEoDRIeChZudW1fb25saW5lX2xvY2FsX25vZGVzGA0gASgNEiEKGWhhc19vcHRlZF9yZXBvcnRfbG9jYXRpb24YDiABKAhCXwoTY29tLmdlZWtzdmlsbGUubWVzaEIKTVFUVFByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [A, L]), ps = /* @__PURE__ */ D(fs, 0), ms = /* @__PURE__ */ D(fs, 1), hs = m({
	PaxcountSchema: () => _s,
	file_paxcount: () => gs
}), gs = /* @__PURE__ */ E("Cg5wYXhjb3VudC5wcm90bxIKbWVzaHRhc3RpYyI1CghQYXhjb3VudBIMCgR3aWZpGAEgASgNEgsKA2JsZRgCIAEoDRIOCgZ1cHRpbWUYAyABKA1CYwoTY29tLmdlZWtzdmlsbGUubWVzaEIOUGF4Y291bnRQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), _s = /* @__PURE__ */ D(gs, 0), vs = m({
	PowerMonSchema: () => bs,
	PowerMon_State: () => xs,
	PowerMon_StateSchema: () => Ss,
	PowerStressMessageSchema: () => Cs,
	PowerStressMessage_Opcode: () => ws,
	PowerStressMessage_OpcodeSchema: () => Ts,
	file_powermon: () => ys
}), ys = /* @__PURE__ */ E("Cg5wb3dlcm1vbi5wcm90bxIKbWVzaHRhc3RpYyLgAQoIUG93ZXJNb24i0wEKBVN0YXRlEggKBE5vbmUQABIRCg1DUFVfRGVlcFNsZWVwEAESEgoOQ1BVX0xpZ2h0U2xlZXAQAhIMCghWZXh0MV9PbhAEEg0KCUxvcmFfUlhPbhAIEg0KCUxvcmFfVFhPbhAQEhEKDUxvcmFfUlhBY3RpdmUQIBIJCgVCVF9PbhBAEgsKBkxFRF9PbhCAARIOCglTY3JlZW5fT24QgAISEwoOU2NyZWVuX0RyYXdpbmcQgAQSDAoHV2lmaV9PbhCACBIPCgpHUFNfQWN0aXZlEIAQIv8CChJQb3dlclN0cmVzc01lc3NhZ2USMgoDY21kGAEgASgOMiUubWVzaHRhc3RpYy5Qb3dlclN0cmVzc01lc3NhZ2UuT3Bjb2RlEhMKC251bV9zZWNvbmRzGAIgASgCIp8CCgZPcGNvZGUSCQoFVU5TRVQQABIOCgpQUklOVF9JTkZPEAESDwoLRk9SQ0VfUVVJRVQQAhINCglFTkRfUVVJRVQQAxINCglTQ1JFRU5fT04QEBIOCgpTQ1JFRU5fT0ZGEBESDAoIQ1BVX0lETEUQIBIRCg1DUFVfREVFUFNMRUVQECESDgoKQ1BVX0ZVTExPThAiEgoKBkxFRF9PThAwEgsKB0xFRF9PRkYQMRIMCghMT1JBX09GRhBAEgsKB0xPUkFfVFgQQRILCgdMT1JBX1JYEEISCgoGQlRfT0ZGEFASCQoFQlRfT04QURIMCghXSUZJX09GRhBgEgsKB1dJRklfT04QYRILCgdHUFNfT0ZGEHASCgoGR1BTX09OEHFCYwoTY29tLmdlZWtzdmlsbGUubWVzaEIOUG93ZXJNb25Qcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), bs = /* @__PURE__ */ D(ys, 0), xs = /* @__PURE__ */ function(e) {
	return e[e.None = 0] = "None", e[e.CPU_DeepSleep = 1] = "CPU_DeepSleep", e[e.CPU_LightSleep = 2] = "CPU_LightSleep", e[e.Vext1_On = 4] = "Vext1_On", e[e.Lora_RXOn = 8] = "Lora_RXOn", e[e.Lora_TXOn = 16] = "Lora_TXOn", e[e.Lora_RXActive = 32] = "Lora_RXActive", e[e.BT_On = 64] = "BT_On", e[e.LED_On = 128] = "LED_On", e[e.Screen_On = 256] = "Screen_On", e[e.Screen_Drawing = 512] = "Screen_Drawing", e[e.Wifi_On = 1024] = "Wifi_On", e[e.GPS_Active = 2048] = "GPS_Active", e;
}({}), Ss = /* @__PURE__ */ w(ys, 0, 0), Cs = /* @__PURE__ */ D(ys, 1), ws = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.PRINT_INFO = 1] = "PRINT_INFO", e[e.FORCE_QUIET = 2] = "FORCE_QUIET", e[e.END_QUIET = 3] = "END_QUIET", e[e.SCREEN_ON = 16] = "SCREEN_ON", e[e.SCREEN_OFF = 17] = "SCREEN_OFF", e[e.CPU_IDLE = 32] = "CPU_IDLE", e[e.CPU_DEEPSLEEP = 33] = "CPU_DEEPSLEEP", e[e.CPU_FULLON = 34] = "CPU_FULLON", e[e.LED_ON = 48] = "LED_ON", e[e.LED_OFF = 49] = "LED_OFF", e[e.LORA_OFF = 64] = "LORA_OFF", e[e.LORA_TX = 65] = "LORA_TX", e[e.LORA_RX = 66] = "LORA_RX", e[e.BT_OFF = 80] = "BT_OFF", e[e.BT_ON = 81] = "BT_ON", e[e.WIFI_OFF = 96] = "WIFI_OFF", e[e.WIFI_ON = 97] = "WIFI_ON", e[e.GPS_OFF = 112] = "GPS_OFF", e[e.GPS_ON = 113] = "GPS_ON", e;
}({}), Ts = /* @__PURE__ */ w(ys, 1, 0), Es = m({
	HardwareMessageSchema: () => Os,
	HardwareMessage_Type: () => ks,
	HardwareMessage_TypeSchema: () => As,
	file_remote_hardware: () => Ds
}), Ds = /* @__PURE__ */ E("ChVyZW1vdGVfaGFyZHdhcmUucHJvdG8SCm1lc2h0YXN0aWMi1gEKD0hhcmR3YXJlTWVzc2FnZRIuCgR0eXBlGAEgASgOMiAubWVzaHRhc3RpYy5IYXJkd2FyZU1lc3NhZ2UuVHlwZRIRCglncGlvX21hc2sYAiABKAQSEgoKZ3Bpb192YWx1ZRgDIAEoBCJsCgRUeXBlEgkKBVVOU0VUEAASDwoLV1JJVEVfR1BJT1MQARIPCgtXQVRDSF9HUElPUxACEhEKDUdQSU9TX0NIQU5HRUQQAxIOCgpSRUFEX0dQSU9TEAQSFAoQUkVBRF9HUElPU19SRVBMWRAFQmMKE2NvbS5nZWVrc3ZpbGxlLm1lc2hCDlJlbW90ZUhhcmR3YXJlWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), Os = /* @__PURE__ */ D(Ds, 0), ks = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.WRITE_GPIOS = 1] = "WRITE_GPIOS", e[e.WATCH_GPIOS = 2] = "WATCH_GPIOS", e[e.GPIOS_CHANGED = 3] = "GPIOS_CHANGED", e[e.READ_GPIOS = 4] = "READ_GPIOS", e[e.READ_GPIOS_REPLY = 5] = "READ_GPIOS_REPLY", e;
}({}), As = /* @__PURE__ */ w(Ds, 0, 0), js = m({
	RTTTLConfigSchema: () => Ns,
	file_rtttl: () => Ms
}), Ms = /* @__PURE__ */ E("CgtydHR0bC5wcm90bxIKbWVzaHRhc3RpYyIfCgtSVFRUTENvbmZpZxIQCghyaW5ndG9uZRgBIAEoCUJmChNjb20uZ2Vla3N2aWxsZS5tZXNoQhFSVFRUTENvbmZpZ1Byb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM"), Ns = /* @__PURE__ */ D(Ms, 0), Ps = m({
	StoreAndForwardSchema: () => Is,
	StoreAndForward_HeartbeatSchema: () => zs,
	StoreAndForward_HistorySchema: () => Rs,
	StoreAndForward_RequestResponse: () => Bs,
	StoreAndForward_RequestResponseSchema: () => Vs,
	StoreAndForward_StatisticsSchema: () => Ls,
	file_storeforward: () => Fs
}), Fs = /* @__PURE__ */ E("ChJzdG9yZWZvcndhcmQucHJvdG8SCm1lc2h0YXN0aWMinAcKD1N0b3JlQW5kRm9yd2FyZBI3CgJychgBIAEoDjIrLm1lc2h0YXN0aWMuU3RvcmVBbmRGb3J3YXJkLlJlcXVlc3RSZXNwb25zZRI3CgVzdGF0cxgCIAEoCzImLm1lc2h0YXN0aWMuU3RvcmVBbmRGb3J3YXJkLlN0YXRpc3RpY3NIABI2CgdoaXN0b3J5GAMgASgLMiMubWVzaHRhc3RpYy5TdG9yZUFuZEZvcndhcmQuSGlzdG9yeUgAEjoKCWhlYXJ0YmVhdBgEIAEoCzIlLm1lc2h0YXN0aWMuU3RvcmVBbmRGb3J3YXJkLkhlYXJ0YmVhdEgAEg4KBHRleHQYBSABKAxIABrNAQoKU3RhdGlzdGljcxIWCg5tZXNzYWdlc190b3RhbBgBIAEoDRIWCg5tZXNzYWdlc19zYXZlZBgCIAEoDRIUCgxtZXNzYWdlc19tYXgYAyABKA0SDwoHdXBfdGltZRgEIAEoDRIQCghyZXF1ZXN0cxgFIAEoDRIYChByZXF1ZXN0c19oaXN0b3J5GAYgASgNEhEKCWhlYXJ0YmVhdBgHIAEoCBISCgpyZXR1cm5fbWF4GAggASgNEhUKDXJldHVybl93aW5kb3cYCSABKA0aSQoHSGlzdG9yeRIYChBoaXN0b3J5X21lc3NhZ2VzGAEgASgNEg4KBndpbmRvdxgCIAEoDRIUCgxsYXN0X3JlcXVlc3QYAyABKA0aLgoJSGVhcnRiZWF0Eg4KBnBlcmlvZBgBIAEoDRIRCglzZWNvbmRhcnkYAiABKA0ivAIKD1JlcXVlc3RSZXNwb25zZRIJCgVVTlNFVBAAEhAKDFJPVVRFUl9FUlJPUhABEhQKEFJPVVRFUl9IRUFSVEJFQVQQAhIPCgtST1VURVJfUElORxADEg8KC1JPVVRFUl9QT05HEAQSDwoLUk9VVEVSX0JVU1kQBRISCg5ST1VURVJfSElTVE9SWRAGEhAKDFJPVVRFUl9TVEFUUxAHEhYKElJPVVRFUl9URVhUX0RJUkVDVBAIEhkKFVJPVVRFUl9URVhUX0JST0FEQ0FTVBAJEhAKDENMSUVOVF9FUlJPUhBAEhIKDkNMSUVOVF9ISVNUT1JZEEESEAoMQ0xJRU5UX1NUQVRTEEISDwoLQ0xJRU5UX1BJTkcQQxIPCgtDTElFTlRfUE9ORxBEEhAKDENMSUVOVF9BQk9SVBBqQgkKB3ZhcmlhbnRCagoTY29tLmdlZWtzdmlsbGUubWVzaEIVU3RvcmVBbmRGb3J3YXJkUHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), Is = /* @__PURE__ */ D(Fs, 0), Ls = /* @__PURE__ */ D(Fs, 0, 0), Rs = /* @__PURE__ */ D(Fs, 0, 1), zs = /* @__PURE__ */ D(Fs, 0, 2), Bs = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.ROUTER_ERROR = 1] = "ROUTER_ERROR", e[e.ROUTER_HEARTBEAT = 2] = "ROUTER_HEARTBEAT", e[e.ROUTER_PING = 3] = "ROUTER_PING", e[e.ROUTER_PONG = 4] = "ROUTER_PONG", e[e.ROUTER_BUSY = 5] = "ROUTER_BUSY", e[e.ROUTER_HISTORY = 6] = "ROUTER_HISTORY", e[e.ROUTER_STATS = 7] = "ROUTER_STATS", e[e.ROUTER_TEXT_DIRECT = 8] = "ROUTER_TEXT_DIRECT", e[e.ROUTER_TEXT_BROADCAST = 9] = "ROUTER_TEXT_BROADCAST", e[e.CLIENT_ERROR = 64] = "CLIENT_ERROR", e[e.CLIENT_HISTORY = 65] = "CLIENT_HISTORY", e[e.CLIENT_STATS = 66] = "CLIENT_STATS", e[e.CLIENT_PING = 67] = "CLIENT_PING", e[e.CLIENT_PONG = 68] = "CLIENT_PONG", e[e.CLIENT_ABORT = 106] = "CLIENT_ABORT", e;
}({}), Vs = /* @__PURE__ */ w(Fs, 0, 0), Hs = {
	broadcastNum: 4294967295,
	minFwVer: 2.2
}, Us = {
	reset: [0, 0],
	bold: [1, 22],
	dim: [2, 22],
	italic: [3, 23],
	underline: [4, 24],
	overline: [53, 55],
	inverse: [7, 27],
	hidden: [8, 28],
	strikethrough: [9, 29],
	black: [30, 39],
	red: [31, 39],
	green: [32, 39],
	yellow: [33, 39],
	blue: [34, 39],
	magenta: [35, 39],
	cyan: [36, 39],
	white: [37, 39],
	blackBright: [90, 39],
	redBright: [91, 39],
	greenBright: [92, 39],
	yellowBright: [93, 39],
	blueBright: [94, 39],
	magentaBright: [95, 39],
	cyanBright: [96, 39],
	whiteBright: [97, 39],
	bgBlack: [40, 49],
	bgRed: [41, 49],
	bgGreen: [42, 49],
	bgYellow: [43, 49],
	bgBlue: [44, 49],
	bgMagenta: [45, 49],
	bgCyan: [46, 49],
	bgWhite: [47, 49],
	bgBlackBright: [100, 49],
	bgRedBright: [101, 49],
	bgGreenBright: [102, 49],
	bgYellowBright: [103, 49],
	bgBlueBright: [104, 49],
	bgMagentaBright: [105, 49],
	bgCyanBright: [106, 49],
	bgWhiteBright: [107, 49]
};
function Ws(e, t, n, r = !1) {
	let i = String(t), a = (e, t) => `\u001b[${t[0]}m${e}\u001b[${t[1]}m`, o = (e, t) => t != null && typeof t == "string" ? a(e, Us[t]) : t != null && Array.isArray(t) ? t.reduce((e, t) => o(e, t), e) : t != null && t[e.trim()] != null ? o(e, t[e.trim()]) : t != null && t["*"] != null ? o(e, t["*"]) : e;
	return i.replace(/{{(.+?)}}/g, (t, i) => {
		let s = n[i] == null ? r ? "" : t : String(n[i]);
		return e.stylePrettyLogs ? o(s, e?.prettyLogStyles?.[i] ?? null) + a("", Us.reset) : s;
	});
}
function V(e, t = 2, n = 0) {
	return e != null && isNaN(e) ? "" : (e = e == null ? e : e + n, t === 2 ? e == null ? "--" : e < 10 ? "0" + e : e.toString() : e == null ? "---" : e < 10 ? "00" + e : e < 100 ? "0" + e : e.toString());
}
function Gs(e) {
	return {
		href: e.href,
		protocol: e.protocol,
		username: e.username,
		password: e.password,
		host: e.host,
		hostname: e.hostname,
		port: e.port,
		pathname: e.pathname,
		search: e.search,
		searchParams: [...e.searchParams].map(([e, t]) => ({
			key: e,
			value: t
		})),
		hash: e.hash,
		origin: e.origin
	};
}
var Ks = {
	getCallerStackFrame: Ys,
	getErrorTrace: Xs,
	getMeta: Js,
	transportJSON: nc,
	transportFormatted: tc,
	isBuffer: rc,
	isError: Qs,
	prettyFormatLogObj: $s,
	prettyFormatErrorObj: ec
}, qs = {
	runtime: "Nodejs",
	runtimeVersion: "",
	hostname: u ? u() : void 0
};
function Js(e, t, n, r, i, a) {
	return Object.assign({}, qs, {
		name: i,
		parentNames: a,
		date: /* @__PURE__ */ new Date(),
		logLevelId: e,
		logLevelName: t,
		path: r ? void 0 : Ys(n)
	});
}
function Ys(e, t = Error()) {
	return Zs(t?.stack?.split("\n")?.filter((e) => e.includes("    at "))?.[e]);
}
function Xs(e) {
	return e?.stack?.split("\n")?.reduce((e, t) => (t.includes("    at ") && e.push(Zs(t)), e), []);
}
function Zs(e) {
	let t = {
		fullFilePath: void 0,
		fileName: void 0,
		fileNameWithLine: void 0,
		fileColumn: void 0,
		fileLine: void 0,
		filePath: void 0,
		filePathWithLine: void 0,
		method: void 0
	};
	if (e != null && e.includes("    at ")) {
		e = e.replace(/^\s+at\s+/gm, "");
		let n = e.split(" ("), r = e?.slice(-1) === ")" ? e?.match(/\(([^)]+)\)/)?.[1] : e, i = r?.includes(":") ? r?.replace("file://", "")?.replace("", "")?.split(":") : void 0, a = i?.pop(), o = i?.pop(), s = i?.pop(), c = d(`${s}:${o}`), l = s?.split("/")?.pop(), u = `${l}:${o}`;
		s != null && s.length > 0 && (t.fullFilePath = r, t.fileName = l, t.fileNameWithLine = u, t.fileColumn = a, t.fileLine = o, t.filePath = s, t.filePathWithLine = c, t.method = n?.[1] == null ? void 0 : n?.[0]);
	}
	return t;
}
function Qs(e) {
	return ee?.isNativeError == null ? e instanceof Error : ee.isNativeError(e);
}
function $s(e, t) {
	return e.reduce((e, n) => (Qs(n) ? e.errors.push(ec(n, t)) : e.args.push(n), e), {
		args: [],
		errors: []
	});
}
function ec(e, t) {
	let n = Xs(e).map((e) => Ws(t, t.prettyErrorStackTemplate, { ...e }, !0)), r = {
		errorName: ` ${e.name} `,
		errorMessage: Object.getOwnPropertyNames(e).reduce((t, n) => (n !== "stack" && t.push(e[n]), t), []).join(", "),
		errorStack: n.join("\n")
	};
	return Ws(t, t.prettyErrorTemplate, r);
}
function tc(e, t, n, r) {
	let i = (n.length > 0 && t.length > 0 ? "\n" : "") + n.join("\n");
	r.prettyInspectOptions.colors = r.stylePrettyLogs, console.log(e + f(r.prettyInspectOptions, ...t) + i);
}
function nc(e) {
	console.log(t(e));
	function t(e) {
		let t = /* @__PURE__ */ new Set();
		return JSON.stringify(e, (e, n) => {
			if (typeof n == "object" && n) {
				if (t.has(n)) return "[Circular]";
				t.add(n);
			}
			return typeof n == "bigint" ? `${n}` : n === void 0 ? "[undefined]" : n;
		});
	}
}
function rc(e) {
	return ((e) => !!(e && (e._isBuffer || e.constructor && e.constructor.isBuffer && e.constructor.isBuffer(e))))(e);
}
var ic = class {
	constructor(e, t, n = 4) {
		this.logObj = t, this.stackDepthLevel = n, this.runtime = Ks, this.settings = {
			type: e?.type ?? "pretty",
			name: e?.name,
			parentNames: e?.parentNames,
			minLevel: e?.minLevel ?? 0,
			argumentsArrayName: e?.argumentsArrayName,
			hideLogPositionForProduction: e?.hideLogPositionForProduction ?? !1,
			prettyLogTemplate: e?.prettyLogTemplate ?? "{{yyyy}}.{{mm}}.{{dd}} {{hh}}:{{MM}}:{{ss}}:{{ms}}	{{logLevelName}}	{{filePathWithLine}}{{nameWithDelimiterPrefix}}	",
			prettyErrorTemplate: e?.prettyErrorTemplate ?? "\n{{errorName}} {{errorMessage}}\nerror stack:\n{{errorStack}}",
			prettyErrorStackTemplate: e?.prettyErrorStackTemplate ?? "  • {{fileName}}	{{method}}\n	{{filePathWithLine}}",
			prettyErrorParentNamesSeparator: e?.prettyErrorParentNamesSeparator ?? ":",
			prettyErrorLoggerNameDelimiter: e?.prettyErrorLoggerNameDelimiter ?? "	",
			stylePrettyLogs: e?.stylePrettyLogs ?? !0,
			prettyLogTimeZone: e?.prettyLogTimeZone ?? "UTC",
			prettyLogStyles: e?.prettyLogStyles ?? {
				logLevelName: {
					"*": [
						"bold",
						"black",
						"bgWhiteBright",
						"dim"
					],
					SILLY: ["bold", "white"],
					TRACE: ["bold", "whiteBright"],
					DEBUG: ["bold", "green"],
					INFO: ["bold", "blue"],
					WARN: ["bold", "yellow"],
					ERROR: ["bold", "red"],
					FATAL: ["bold", "redBright"]
				},
				dateIsoStr: "white",
				filePathWithLine: "white",
				name: ["white", "bold"],
				nameWithDelimiterPrefix: ["white", "bold"],
				nameWithDelimiterSuffix: ["white", "bold"],
				errorName: [
					"bold",
					"bgRedBright",
					"whiteBright"
				],
				fileName: ["yellow"],
				fileNameWithLine: "white"
			},
			prettyInspectOptions: e?.prettyInspectOptions ?? {
				colors: !0,
				compact: !1,
				depth: Infinity
			},
			metaProperty: e?.metaProperty ?? "_meta",
			maskPlaceholder: e?.maskPlaceholder ?? "[***]",
			maskValuesOfKeys: e?.maskValuesOfKeys ?? ["password"],
			maskValuesOfKeysCaseInsensitive: e?.maskValuesOfKeysCaseInsensitive ?? !1,
			maskValuesRegEx: e?.maskValuesRegEx,
			prefix: [...e?.prefix ?? []],
			attachedTransports: [...e?.attachedTransports ?? []],
			overwrite: {
				mask: e?.overwrite?.mask,
				toLogObj: e?.overwrite?.toLogObj,
				addMeta: e?.overwrite?.addMeta,
				addPlaceholders: e?.overwrite?.addPlaceholders,
				formatMeta: e?.overwrite?.formatMeta,
				formatLogObj: e?.overwrite?.formatLogObj,
				transportFormatted: e?.overwrite?.transportFormatted,
				transportJSON: e?.overwrite?.transportJSON
			}
		};
	}
	log(e, t, ...n) {
		if (e < this.settings.minLevel) return;
		let r = [...this.settings.prefix, ...n], i = this.settings.overwrite?.mask == null ? this.settings.maskValuesOfKeys != null && this.settings.maskValuesOfKeys.length > 0 ? this._mask(r) : r : this.settings.overwrite?.mask(r), a = this.logObj == null ? void 0 : this._recursiveCloneAndExecuteFunctions(this.logObj), o = this.settings.overwrite?.toLogObj == null ? this._toLogObj(i, a) : this.settings.overwrite?.toLogObj(i, a), s = this.settings.overwrite?.addMeta == null ? this._addMetaToLogObj(o, e, t) : this.settings.overwrite?.addMeta(o, e, t), c, l;
		return this.settings.overwrite?.formatMeta != null && (c = this.settings.overwrite?.formatMeta(s?.[this.settings.metaProperty])), this.settings.overwrite?.formatLogObj != null && (l = this.settings.overwrite?.formatLogObj(i, this.settings)), this.settings.type === "pretty" && (c ??= this._prettyFormatLogObjMeta(s?.[this.settings.metaProperty]), l ??= this.runtime.prettyFormatLogObj(i, this.settings)), c != null && l != null ? this.settings.overwrite?.transportFormatted == null ? this.runtime.transportFormatted(c, l.args, l.errors, this.settings) : this.settings.overwrite?.transportFormatted(c, l.args, l.errors, this.settings) : this.settings.overwrite?.transportJSON == null ? this.settings.type !== "hidden" && this.runtime.transportJSON(s) : this.settings.overwrite?.transportJSON(s), this.settings.attachedTransports != null && this.settings.attachedTransports.length > 0 && this.settings.attachedTransports.forEach((e) => {
			e(s);
		}), s;
	}
	attachTransport(e) {
		this.settings.attachedTransports.push(e);
	}
	getSubLogger(e, t) {
		let n = {
			...this.settings,
			...e,
			parentNames: this.settings?.parentNames != null && this.settings?.name != null ? [...this.settings.parentNames, this.settings.name] : this.settings?.name == null ? void 0 : [this.settings.name],
			prefix: [...this.settings.prefix, ...e?.prefix ?? []]
		};
		return new this.constructor(n, t ?? this.logObj, this.stackDepthLevel);
	}
	_mask(e) {
		let t = this.settings.maskValuesOfKeysCaseInsensitive === !0 ? this.settings.maskValuesOfKeys.map((e) => e.toLowerCase()) : this.settings.maskValuesOfKeys;
		return e?.map((e) => this._recursiveCloneAndMaskValuesOfKeys(e, t));
	}
	_recursiveCloneAndMaskValuesOfKeys(e, t, n = []) {
		if (n.includes(e)) return { ...e };
		if (typeof e == "object" && e && n.push(e), this.runtime.isError(e) || this.runtime.isBuffer(e)) return e;
		if (e instanceof Map) return new Map(e);
		if (e instanceof Set) return new Set(e);
		if (Array.isArray(e)) return e.map((e) => this._recursiveCloneAndMaskValuesOfKeys(e, t, n));
		if (e instanceof Date) return new Date(e.getTime());
		if (e instanceof URL) return Gs(e);
		if (typeof e == "object" && e) {
			let r = this.runtime.isError(e) ? this._cloneError(e) : Object.create(Object.getPrototypeOf(e));
			return Object.getOwnPropertyNames(e).reduce((r, i) => (r[i] = t.includes(this.settings?.maskValuesOfKeysCaseInsensitive === !0 ? i.toLowerCase() : i) ? this.settings.maskPlaceholder : (() => {
				try {
					return this._recursiveCloneAndMaskValuesOfKeys(e[i], t, n);
				} catch {
					return null;
				}
			})(), r), r);
		}
		if (typeof e == "string") {
			let t = e;
			for (let e of this.settings?.maskValuesRegEx || []) t = t.replace(e, this.settings?.maskPlaceholder || "");
			return t;
		}
		return e;
	}
	_recursiveCloneAndExecuteFunctions(e, t = []) {
		return this.isObjectOrArray(e) && t.includes(e) ? this.shallowCopy(e) : (this.isObjectOrArray(e) && t.push(e), Array.isArray(e) ? e.map((e) => this._recursiveCloneAndExecuteFunctions(e, t)) : e instanceof Date ? new Date(e.getTime()) : this.isObject(e) ? Object.getOwnPropertyNames(e).reduce((n, r) => {
			let i = Object.getOwnPropertyDescriptor(e, r);
			if (i) {
				Object.defineProperty(n, r, i);
				let a = e[r];
				n[r] = typeof a == "function" ? a() : this._recursiveCloneAndExecuteFunctions(a, t);
			}
			return n;
		}, Object.create(Object.getPrototypeOf(e))) : e);
	}
	isObjectOrArray(e) {
		return typeof e == "object" && !!e;
	}
	isObject(e) {
		return typeof e == "object" && !Array.isArray(e) && e !== null;
	}
	shallowCopy(e) {
		return Array.isArray(e) ? [...e] : { ...e };
	}
	_toLogObj(e, t = {}) {
		return e = e?.map((e) => this.runtime.isError(e) ? this._toErrorObject(e) : e), t = this.settings.argumentsArrayName == null ? e.length === 1 && !Array.isArray(e[0]) && this.runtime.isBuffer(e[0]) !== !0 && !(e[0] instanceof Date) ? typeof e[0] == "object" && e[0] != null ? {
			...e[0],
			...t
		} : {
			0: e[0],
			...t
		} : {
			...t,
			...e
		} : {
			...t,
			[this.settings.argumentsArrayName]: e
		}, t;
	}
	_cloneError(e) {
		let t = new e.constructor();
		return Object.getOwnPropertyNames(e).forEach((n) => {
			t[n] = e[n];
		}), t;
	}
	_toErrorObject(e) {
		return {
			nativeError: e,
			name: e.name ?? "Error",
			message: e.message,
			stack: this.runtime.getErrorTrace(e)
		};
	}
	_addMetaToLogObj(e, t, n) {
		return {
			...e,
			[this.settings.metaProperty]: this.runtime.getMeta(t, n, this.stackDepthLevel, this.settings.hideLogPositionForProduction, this.settings.name, this.settings.parentNames)
		};
	}
	_prettyFormatLogObjMeta(e) {
		if (e == null) return "";
		let t = this.settings.prettyLogTemplate, n = {};
		t.includes("{{yyyy}}.{{mm}}.{{dd}} {{hh}}:{{MM}}:{{ss}}:{{ms}}") ? t = t.replace("{{yyyy}}.{{mm}}.{{dd}} {{hh}}:{{MM}}:{{ss}}:{{ms}}", "{{dateIsoStr}}") : this.settings.prettyLogTimeZone === "UTC" ? (n.yyyy = e?.date?.getUTCFullYear() ?? "----", n.mm = V(e?.date?.getUTCMonth(), 2, 1), n.dd = V(e?.date?.getUTCDate(), 2), n.hh = V(e?.date?.getUTCHours(), 2), n.MM = V(e?.date?.getUTCMinutes(), 2), n.ss = V(e?.date?.getUTCSeconds(), 2), n.ms = V(e?.date?.getUTCMilliseconds(), 3)) : (n.yyyy = e?.date?.getFullYear() ?? "----", n.mm = V(e?.date?.getMonth(), 2, 1), n.dd = V(e?.date?.getDate(), 2), n.hh = V(e?.date?.getHours(), 2), n.MM = V(e?.date?.getMinutes(), 2), n.ss = V(e?.date?.getSeconds(), 2), n.ms = V(e?.date?.getMilliseconds(), 3));
		let r = this.settings.prettyLogTimeZone === "UTC" ? e?.date : /* @__PURE__ */ new Date(e?.date?.getTime() - e?.date?.getTimezoneOffset() * 6e4);
		n.rawIsoStr = r?.toISOString(), n.dateIsoStr = r?.toISOString().replace("T", " ").replace("Z", ""), n.logLevelName = e?.logLevelName, n.fileNameWithLine = e?.path?.fileNameWithLine ?? "", n.filePathWithLine = e?.path?.filePathWithLine ?? "", n.fullFilePath = e?.path?.fullFilePath ?? "";
		let i = this.settings.parentNames?.join(this.settings.prettyErrorParentNamesSeparator);
		return i = i != null && e?.name != null ? i + this.settings.prettyErrorParentNamesSeparator : void 0, n.name = e?.name != null || i != null ? (i ?? "") + e?.name ?? "" : "", n.nameWithDelimiterPrefix = n.name.length > 0 ? this.settings.prettyErrorLoggerNameDelimiter + n.name : "", n.nameWithDelimiterSuffix = n.name.length > 0 ? n.name + this.settings.prettyErrorLoggerNameDelimiter : "", this.settings.overwrite?.addPlaceholders != null && this.settings.overwrite?.addPlaceholders(e, n), Ws(this.settings, t, n);
	}
}, ac = class extends ic {
	constructor(e, t) {
		let n = typeof window < "u" && typeof document < "u", r = n ? window.chrome !== void 0 && window.CSS !== void 0 && window.CSS.supports("color", "green") : !1, i = n ? /^((?!chrome|android).)*safari/i.test(navigator.userAgent) : !1;
		e ||= {}, e.stylePrettyLogs = e.stylePrettyLogs && n && !r ? !1 : e.stylePrettyLogs, super(e, t, i ? 4 : 5);
	}
	log(e, t, ...n) {
		return super.log(e, t, ...n);
	}
	silly(...e) {
		return super.log(0, "SILLY", ...e);
	}
	trace(...e) {
		return super.log(1, "TRACE", ...e);
	}
	debug(...e) {
		return super.log(2, "DEBUG", ...e);
	}
	info(...e) {
		return super.log(3, "INFO", ...e);
	}
	warn(...e) {
		return super.log(4, "WARN", ...e);
	}
	error(...e) {
		return super.log(5, "ERROR", ...e);
	}
	fatal(...e) {
		return super.log(6, "FATAL", ...e);
	}
	getSubLogger(e, t) {
		return super.getSubLogger(e, t);
	}
}, H = s({
	ChannelNumber: () => sc,
	DeviceStatusEnum: () => U,
	Emitter: () => W,
	EmitterScope: () => oc
}), U = /* @__PURE__ */ function(e) {
	return e[e.DeviceRestarting = 1] = "DeviceRestarting", e[e.DeviceDisconnected = 2] = "DeviceDisconnected", e[e.DeviceConnecting = 3] = "DeviceConnecting", e[e.DeviceReconnecting = 4] = "DeviceReconnecting", e[e.DeviceConnected = 5] = "DeviceConnected", e[e.DeviceConfiguring = 6] = "DeviceConfiguring", e[e.DeviceConfigured = 7] = "DeviceConfigured", e;
}({}), oc = /* @__PURE__ */ function(e) {
	return e[e.MeshDevice = 1] = "MeshDevice", e[e.SerialConnection = 2] = "SerialConnection", e[e.NodeSerialConnection = 3] = "NodeSerialConnection", e[e.BleConnection = 4] = "BleConnection", e[e.HttpConnection = 5] = "HttpConnection", e;
}({}), W = /* @__PURE__ */ function(e) {
	return e[e.Constructor = 0] = "Constructor", e[e.SendText = 1] = "SendText", e[e.SendWaypoint = 2] = "SendWaypoint", e[e.SendPacket = 3] = "SendPacket", e[e.SendRaw = 4] = "SendRaw", e[e.SetConfig = 5] = "SetConfig", e[e.SetModuleConfig = 6] = "SetModuleConfig", e[e.ConfirmSetConfig = 7] = "ConfirmSetConfig", e[e.SetOwner = 8] = "SetOwner", e[e.SetChannel = 9] = "SetChannel", e[e.ConfirmSetChannel = 10] = "ConfirmSetChannel", e[e.ClearChannel = 11] = "ClearChannel", e[e.GetChannel = 12] = "GetChannel", e[e.GetAllChannels = 13] = "GetAllChannels", e[e.GetConfig = 14] = "GetConfig", e[e.GetModuleConfig = 15] = "GetModuleConfig", e[e.GetOwner = 16] = "GetOwner", e[e.Configure = 17] = "Configure", e[e.HandleFromRadio = 18] = "HandleFromRadio", e[e.HandleMeshPacket = 19] = "HandleMeshPacket", e[e.Connect = 20] = "Connect", e[e.Ping = 21] = "Ping", e[e.ReadFromRadio = 22] = "ReadFromRadio", e[e.WriteToRadio = 23] = "WriteToRadio", e[e.SetDebugMode = 24] = "SetDebugMode", e[e.GetMetadata = 25] = "GetMetadata", e[e.ResetNodes = 26] = "ResetNodes", e[e.Shutdown = 27] = "Shutdown", e[e.Reboot = 28] = "Reboot", e[e.RebootOta = 29] = "RebootOta", e[e.FactoryReset = 30] = "FactoryReset", e[e.EnterDfuMode = 31] = "EnterDfuMode", e[e.RemoveNodeByNum = 32] = "RemoveNodeByNum", e[e.SetCannedMessages = 33] = "SetCannedMessages", e[e.Disconnect = 34] = "Disconnect", e[e.ConnectionStatus = 35] = "ConnectionStatus", e;
}({}), sc = /* @__PURE__ */ function(e) {
	return e[e.Primary = 0] = "Primary", e[e.Channel1 = 1] = "Channel1", e[e.Channel2 = 2] = "Channel2", e[e.Channel3 = 3] = "Channel3", e[e.Channel4 = 4] = "Channel4", e[e.Channel5 = 5] = "Channel5", e[e.Channel6 = 6] = "Channel6", e[e.Admin = 7] = "Admin", e;
}({}), cc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/DispatcherWrapper.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.DispatcherWrapper = class {
		constructor(e) {
			this._subscribe = (t) => e.subscribe(t), this._unsubscribe = (t) => e.unsubscribe(t), this._one = (t) => e.one(t), this._has = (t) => e.has(t), this._clear = () => e.clear(), this._count = () => e.count, this._onSubscriptionChange = () => e.onSubscriptionChange;
		}
		get onSubscriptionChange() {
			return this._onSubscriptionChange();
		}
		get count() {
			return this._count();
		}
		subscribe(e) {
			return this._subscribe(e);
		}
		sub(e) {
			return this.subscribe(e);
		}
		unsubscribe(e) {
			this._unsubscribe(e);
		}
		unsub(e) {
			this.unsubscribe(e);
		}
		one(e) {
			return this._one(e);
		}
		has(e) {
			return this._has(e);
		}
		clear() {
			this._clear();
		}
	};
}) }), lc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/events/Subscription.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.Subscription = class {
		constructor(e, t) {
			this.handler = e, this.isOnce = t, this.isExecuted = !1;
		}
		execute(e, t, n) {
			if (!this.isOnce || !this.isExecuted) {
				this.isExecuted = !0;
				var r = this.handler;
				e ? setTimeout(() => {
					r.apply(t, n);
				}, 1) : r.apply(t, n);
			}
		}
	};
}) }), uc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/management/EventManagement.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.EventManagement = class {
		constructor(e) {
			this.unsub = e, this.propagationStopped = !1;
		}
		stopPropagation() {
			this.propagationStopped = !0;
		}
	};
}) }), dc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/DispatcherBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = cc(), n = lc(), r = uc();
	var i = class {
		constructor() {
			this._subscriptions = [];
		}
		get count() {
			return this._subscriptions.length;
		}
		get onSubscriptionChange() {
			return this._onSubscriptionChange ??= new a(), this._onSubscriptionChange.asEvent();
		}
		subscribe(e) {
			return e && (this._subscriptions.push(this.createSubscription(e, !1)), this.triggerSubscriptionChange()), () => {
				this.unsubscribe(e);
			};
		}
		sub(e) {
			return this.subscribe(e);
		}
		one(e) {
			return e && (this._subscriptions.push(this.createSubscription(e, !0)), this.triggerSubscriptionChange()), () => {
				this.unsubscribe(e);
			};
		}
		has(e) {
			return e ? this._subscriptions.some((t) => t.handler == e) : !1;
		}
		unsubscribe(e) {
			if (!e) return;
			let t = !1;
			for (let n = 0; n < this._subscriptions.length; n++) if (this._subscriptions[n].handler == e) {
				this._subscriptions.splice(n, 1), t = !0;
				break;
			}
			t && this.triggerSubscriptionChange();
		}
		unsub(e) {
			this.unsubscribe(e);
		}
		_dispatch(e, t, n) {
			for (let i of [...this._subscriptions]) {
				let a = new r.EventManagement(() => this.unsub(i.handler)), o = Array.prototype.slice.call(n);
				if (o.push(a), i.execute(e, t, o), this.cleanup(i), !e && a.propagationStopped) return { propagationStopped: !0 };
			}
			return e ? null : { propagationStopped: !1 };
		}
		createSubscription(e, t) {
			return new n.Subscription(e, t);
		}
		cleanup(e) {
			let t = !1;
			if (e.isOnce && e.isExecuted) {
				let n = this._subscriptions.indexOf(e);
				n > -1 && (this._subscriptions.splice(n, 1), t = !0);
			}
			t && this.triggerSubscriptionChange();
		}
		asEvent() {
			return this._wrap ??= new t.DispatcherWrapper(this), this._wrap;
		}
		clear() {
			this._subscriptions.length != 0 && (this._subscriptions.splice(0, this._subscriptions.length), this.triggerSubscriptionChange());
		}
		triggerSubscriptionChange() {
			this._onSubscriptionChange != null && this._onSubscriptionChange.dispatch(this.count);
		}
	};
	e.DispatcherBase = i;
	var a = class extends i {
		dispatch(e) {
			this._dispatch(!1, this, arguments);
		}
	};
	e.SubscriptionChangeEventDispatcher = a;
}) }), fc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/DispatchError.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.DispatchError = class extends Error {
		constructor(e) {
			super(e);
		}
	};
}) }), pc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/EventListBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.EventListBase = class {
		constructor() {
			this._events = {};
		}
		get(e) {
			let t = this._events[e];
			return t || (t = this.createDispatcher(), this._events[e] = t, t);
		}
		remove(e) {
			delete this._events[e];
		}
	};
}) }), mc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/handling/HandlingBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.HandlingBase = class {
		constructor(e) {
			this.events = e;
		}
		one(e, t) {
			this.events.get(e).one(t);
		}
		has(e, t) {
			return this.events.get(e).has(t);
		}
		subscribe(e, t) {
			this.events.get(e).subscribe(t);
		}
		sub(e, t) {
			this.subscribe(e, t);
		}
		unsubscribe(e, t) {
			this.events.get(e).unsubscribe(t);
		}
		unsub(e, t) {
			this.unsubscribe(e, t);
		}
	};
}) }), hc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/events/PromiseSubscription.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.PromiseSubscription = class {
		constructor(e, t) {
			this.handler = e, this.isOnce = t, this.isExecuted = !1;
		}
		async execute(e, t, n) {
			if (!this.isOnce || !this.isExecuted) {
				this.isExecuted = !0;
				var r = this.handler;
				if (e) {
					setTimeout(() => {
						r.apply(t, n);
					}, 1);
					return;
				}
				await r.apply(t, n);
			}
		}
	};
}) }), gc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/PromiseDispatcherBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = hc(), n = uc(), r = dc(), i = fc();
	e.PromiseDispatcherBase = class extends r.DispatcherBase {
		_dispatch(e, t, n) {
			throw new i.DispatchError("_dispatch not supported. Use _dispatchAsPromise.");
		}
		createSubscription(e, n) {
			return new t.PromiseSubscription(e, n);
		}
		async _dispatchAsPromise(e, t, r) {
			for (let i of [...this._subscriptions]) {
				let a = new n.EventManagement(() => this.unsub(i.handler)), o = Array.prototype.slice.call(r);
				if (o.push(a), await i.execute(e, t, o), this.cleanup(i), !e && a.propagationStopped) return { propagationStopped: !0 };
			}
			return e ? null : { propagationStopped: !1 };
		}
	};
}) }), _c = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/index.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.SubscriptionChangeEventDispatcher = e.HandlingBase = e.PromiseDispatcherBase = e.PromiseSubscription = e.DispatchError = e.EventManagement = e.EventListBase = e.DispatcherWrapper = e.DispatcherBase = e.Subscription = void 0;
	let t = dc();
	Object.defineProperty(e, "DispatcherBase", {
		enumerable: !0,
		get: function() {
			return t.DispatcherBase;
		}
	}), Object.defineProperty(e, "SubscriptionChangeEventDispatcher", {
		enumerable: !0,
		get: function() {
			return t.SubscriptionChangeEventDispatcher;
		}
	});
	let n = fc();
	Object.defineProperty(e, "DispatchError", {
		enumerable: !0,
		get: function() {
			return n.DispatchError;
		}
	});
	let r = cc();
	Object.defineProperty(e, "DispatcherWrapper", {
		enumerable: !0,
		get: function() {
			return r.DispatcherWrapper;
		}
	});
	let i = pc();
	Object.defineProperty(e, "EventListBase", {
		enumerable: !0,
		get: function() {
			return i.EventListBase;
		}
	});
	let a = uc();
	Object.defineProperty(e, "EventManagement", {
		enumerable: !0,
		get: function() {
			return a.EventManagement;
		}
	});
	let o = mc();
	Object.defineProperty(e, "HandlingBase", {
		enumerable: !0,
		get: function() {
			return o.HandlingBase;
		}
	});
	let s = gc();
	Object.defineProperty(e, "PromiseDispatcherBase", {
		enumerable: !0,
		get: function() {
			return s.PromiseDispatcherBase;
		}
	});
	let c = hc();
	Object.defineProperty(e, "PromiseSubscription", {
		enumerable: !0,
		get: function() {
			return c.PromiseSubscription;
		}
	});
	let l = lc();
	Object.defineProperty(e, "Subscription", {
		enumerable: !0,
		get: function() {
			return l.Subscription;
		}
	});
}) }), vc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/SimpleEventDispatcher.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = _c();
	e.SimpleEventDispatcher = class extends t.DispatcherBase {
		constructor() {
			super();
		}
		dispatch(e) {
			let n = this._dispatch(!1, this, arguments);
			if (n == null) throw new t.DispatchError("Got `null` back from dispatch.");
			return n;
		}
		dispatchAsync(e) {
			this._dispatch(!0, this, arguments);
		}
		asEvent() {
			return super.asEvent();
		}
	};
}) }), yc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/SimpleEventList.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = _c(), n = vc();
	e.SimpleEventList = class extends t.EventListBase {
		constructor() {
			super();
		}
		createDispatcher() {
			return new n.SimpleEventDispatcher();
		}
	};
}) }), bc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/SimpleEventHandlingBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = _c(), n = yc();
	e.SimpleEventHandlingBase = class extends t.HandlingBase {
		constructor() {
			super(new n.SimpleEventList());
		}
	};
}) }), xc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/NonUniformSimpleEventList.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = vc();
	e.NonUniformSimpleEventList = class {
		constructor() {
			this._events = {};
		}
		get(e) {
			if (this._events[e]) return this._events[e];
			let t = this.createDispatcher();
			return this._events[e] = t, t;
		}
		remove(e) {
			delete this._events[e];
		}
		createDispatcher() {
			return new t.SimpleEventDispatcher();
		}
	};
}) }), Sc = /* @__PURE__ */ o({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/index.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.NonUniformSimpleEventList = e.SimpleEventList = e.SimpleEventHandlingBase = e.SimpleEventDispatcher = void 0;
	let t = vc();
	Object.defineProperty(e, "SimpleEventDispatcher", {
		enumerable: !0,
		get: function() {
			return t.SimpleEventDispatcher;
		}
	});
	let n = bc();
	Object.defineProperty(e, "SimpleEventHandlingBase", {
		enumerable: !0,
		get: function() {
			return n.SimpleEventHandlingBase;
		}
	});
	let r = xc();
	Object.defineProperty(e, "NonUniformSimpleEventList", {
		enumerable: !0,
		get: function() {
			return r.NonUniformSimpleEventList;
		}
	});
	let i = yc();
	Object.defineProperty(e, "SimpleEventList", {
		enumerable: !0,
		get: function() {
			return i.SimpleEventList;
		}
	});
}) }), G = /* @__PURE__ */ l(Sc(), 1), Cc = class {
	constructor() {
		this.onLogEvent = new G.SimpleEventDispatcher(), this.onFromRadio = new G.SimpleEventDispatcher(), this.onMeshPacket = new G.SimpleEventDispatcher(), this.onMyNodeInfo = new G.SimpleEventDispatcher(), this.onNodeInfoPacket = new G.SimpleEventDispatcher(), this.onChannelPacket = new G.SimpleEventDispatcher(), this.onConfigPacket = new G.SimpleEventDispatcher(), this.onModuleConfigPacket = new G.SimpleEventDispatcher(), this.onAtakPacket = new G.SimpleEventDispatcher(), this.onMessagePacket = new G.SimpleEventDispatcher(), this.onRemoteHardwarePacket = new G.SimpleEventDispatcher(), this.onPositionPacket = new G.SimpleEventDispatcher(), this.onUserPacket = new G.SimpleEventDispatcher(), this.onRoutingPacket = new G.SimpleEventDispatcher(), this.onDeviceMetadataPacket = new G.SimpleEventDispatcher(), this.onCannedMessageModulePacket = new G.SimpleEventDispatcher(), this.onWaypointPacket = new G.SimpleEventDispatcher(), this.onAudioPacket = new G.SimpleEventDispatcher(), this.onDetectionSensorPacket = new G.SimpleEventDispatcher(), this.onPingPacket = new G.SimpleEventDispatcher(), this.onIpTunnelPacket = new G.SimpleEventDispatcher(), this.onPaxcounterPacket = new G.SimpleEventDispatcher(), this.onSerialPacket = new G.SimpleEventDispatcher(), this.onStoreForwardPacket = new G.SimpleEventDispatcher(), this.onRangeTestPacket = new G.SimpleEventDispatcher(), this.onTelemetryPacket = new G.SimpleEventDispatcher(), this.onZpsPacket = new G.SimpleEventDispatcher(), this.onSimulatorPacket = new G.SimpleEventDispatcher(), this.onTraceRoutePacket = new G.SimpleEventDispatcher(), this.onNeighborInfoPacket = new G.SimpleEventDispatcher(), this.onAtakPluginPacket = new G.SimpleEventDispatcher(), this.onMapReportPacket = new G.SimpleEventDispatcher(), this.onPrivatePacket = new G.SimpleEventDispatcher(), this.onAtakForwarderPacket = new G.SimpleEventDispatcher(), this.onClientNotificationPacket = new G.SimpleEventDispatcher(), this.onDeviceStatus = new G.SimpleEventDispatcher(), this.onLogRecord = new G.SimpleEventDispatcher(), this.onMeshHeartbeat = new G.SimpleEventDispatcher(), this.onDeviceDebugLog = new G.SimpleEventDispatcher(), this.onPendingSettingsChange = new G.SimpleEventDispatcher(), this.onQueueStatus = new G.SimpleEventDispatcher();
	}
}, wc = /* @__PURE__ */ l(Sc(), 1), Tc = class {
	constructor() {
		this.queue = [], this.lock = !1, this.ackNotifier = new wc.SimpleEventDispatcher(), this.errorNotifier = new wc.SimpleEventDispatcher(), this.timeout = 6e4;
	}
	getState() {
		return this.queue;
	}
	clear() {
		this.queue = [];
	}
	push(e) {
		let t = {
			...e,
			sent: !1,
			added: /* @__PURE__ */ new Date(),
			promise: new Promise((t, n) => {
				this.ackNotifier.subscribe((n) => {
					e.id === n && (this.remove(e.id), t(n));
				}), this.errorNotifier.subscribe((t) => {
					e.id === t.id && (this.remove(e.id), n(t));
				}), setTimeout(() => {
					if (this.queue.findIndex((t) => t.id === e.id) !== -1) {
						this.remove(e.id);
						let r = T(I.ToRadioSchema, e.data);
						if (r.payloadVariant.case === "heartbeat" || r.payloadVariant.case === "wantConfigId") {
							t(e.id);
							return;
						}
						console.warn(`Packet ${e.id} of type ${r.payloadVariant.case} timed out`), n({
							id: e.id,
							error: I.Routing_Error.TIMEOUT
						});
					}
				}, this.timeout);
			})
		};
		this.queue.push(t);
	}
	remove(e) {
		this.lock ? setTimeout(() => this.remove(e), 100) : this.queue = this.queue.filter((t) => t.id !== e);
	}
	processAck(e) {
		this.ackNotifier.dispatch(e);
	}
	processError(e) {
		console.error(`Error received for packet ${e.id}: ${I.Routing_Error[e.error]}`), this.errorNotifier.dispatch(e);
	}
	wait(e) {
		let t = this.queue.find((t) => t.id === e);
		if (!t) throw Error("Packet does not exist");
		return t.promise;
	}
	async processQueue(e) {
		if (this.lock) return;
		this.lock = !0;
		let t = e.getWriter();
		try {
			for (; this.queue.filter((e) => !e.sent).length > 0;) {
				let e = this.queue.filter((e) => !e.sent)[0];
				if (e) {
					await new Promise((e) => setTimeout(e, 200));
					try {
						await t.write(e.data), e.sent = !0;
					} catch (n) {
						if (n?.code === "ECONNRESET" || n?.code === "ERR_INVALID_STATE") throw t.releaseLock(), this.lock = !1, n;
						console.error(`Error sending packet ${e.id}`, n);
					}
				}
			}
		} finally {
			t.releaseLock(), this.lock = !1;
		}
	}
}, Ec = () => {
	let e = new Uint8Array([]), t = new TextDecoder();
	return new TransformStream({ transform(n, r) {
		e = new Uint8Array([...e, ...n]);
		let i = !1;
		for (; e.length !== 0 && !i;) {
			let n = e.findIndex((e) => e === 148);
			if (e[n + 1] === 195) {
				e.subarray(0, n).length && (r.enqueue({
					type: "debug",
					data: t.decode(e.subarray(0, n))
				}), e = e.subarray(n));
				let a = e[2], o = e[3];
				if (a !== void 0 && o !== void 0 && e.length >= 4 + (a << 8) + o) {
					let t = e.subarray(4, 4 + (a << 8) + o), n = t.findIndex((e) => e === 148);
					n !== -1 && t[n + 1] === 195 ? (console.warn(`⚠️ Malformed packet found, discarding: ${e.subarray(0, n - 1).toString()}`), e = e.subarray(n)) : (e = e.subarray(3 + (a << 8) + o + 1), r.enqueue({
						type: "packet",
						data: t
					}));
				} else i = !0;
			} else i = !0;
		}
	} });
}, Dc = () => new TransformStream({ transform(e, t) {
	let n = e.length, r = new Uint8Array([
		148,
		195,
		n >> 8 & 255,
		n & 255
	]);
	t.enqueue(new Uint8Array([...r, ...e]));
} }), Oc = class {
	constructor(e) {
		this.sendRaw = e, this.rxBuffer = [], this.txBuffer = [], this.textEncoder = new TextEncoder(), this.counter = 0;
	}
	async downloadFile(e) {
		return await this.sendCommand(F.XModem_Control.STX, this.textEncoder.encode(e), 0);
	}
	async uploadFile(e, t) {
		for (let e = 0; e < t.length; e += 128) this.txBuffer.push(t.slice(e, e + 128));
		return await this.sendCommand(F.XModem_Control.SOH, this.textEncoder.encode(e), 0);
	}
	async sendCommand(e, t, n, r) {
		let i = S(I.ToRadioSchema, { payloadVariant: {
			case: "xmodemPacket",
			value: {
				buffer: t,
				control: e,
				seq: n,
				crc16: r
			}
		} });
		return await this.sendRaw(C(I.ToRadioSchema, i));
	}
	async handlePacket(e) {
		switch (await new Promise((e) => setTimeout(e, 100)), e.control) {
			case F.XModem_Control.NUL: break;
			case F.XModem_Control.SOH: return this.counter = e.seq, this.validateCrc16(e) ? (this.rxBuffer[this.counter] = e.buffer, this.sendCommand(F.XModem_Control.ACK)) : await this.sendCommand(F.XModem_Control.NAK, void 0, e.seq);
			case F.XModem_Control.STX: break;
			case F.XModem_Control.EOT: break;
			case F.XModem_Control.ACK:
				if (this.counter++, this.txBuffer[this.counter - 1]) return this.sendCommand(F.XModem_Control.SOH, this.txBuffer[this.counter - 1], this.counter, te(this.txBuffer[this.counter - 1] ?? /* @__PURE__ */ new Uint8Array()));
				if (this.counter === this.txBuffer.length + 1) return this.sendCommand(F.XModem_Control.EOT);
				this.clear();
				break;
			case F.XModem_Control.NAK: return this.sendCommand(F.XModem_Control.SOH, this.txBuffer[this.counter], this.counter, te(this.txBuffer[this.counter - 1] ?? /* @__PURE__ */ new Uint8Array()));
			case F.XModem_Control.CAN:
				this.clear();
				break;
			case F.XModem_Control.CTRLZ:
		}
		return Promise.resolve(0);
	}
	validateCrc16(e) {
		return te(e.buffer) === e.crc16;
	}
	clear() {
		this.counter = 0, this.rxBuffer = [], this.txBuffer = [];
	}
}, kc = s({
	EventSystem: () => Cc,
	Queue: () => Tc,
	Xmodem: () => Oc,
	fromDeviceStream: () => Ec,
	toDeviceStream: () => Dc
}), Ac = (e) => new WritableStream({ write(t) {
	switch (t.type) {
		case "status": {
			let { status: n, reason: r } = t.data;
			e.updateDeviceStatus(n), e.log.info(W[W.ConnectionStatus], `🔗 ${U[n]} ${r ? `(${r})` : ""}`);
			break;
		}
		case "debug": break;
		case "packet": {
			let n;
			try {
				n = T(I.FromRadioSchema, t.data);
			} catch (t) {
				e.log.error(W[W.HandleFromRadio], "⚠️  Received undecodable packet", t);
				break;
			}
			switch (e.events.onFromRadio.dispatch(n), n.payloadVariant.case) {
				case "packet":
					try {
						e.handleMeshPacket(n.payloadVariant.value);
					} catch (t) {
						e.log.error(W[W.HandleFromRadio], "⚠️  Unable to handle mesh packet", t);
					}
					break;
				case "myInfo":
					e.events.onMyNodeInfo.dispatch(n.payloadVariant.value), e.log.info(W[W.HandleFromRadio], "📱 Received Node info for this device");
					break;
				case "nodeInfo":
					e.log.info(W[W.HandleFromRadio], `📱 Received Node Info packet for node: ${n.payloadVariant.value.num}`), e.events.onNodeInfoPacket.dispatch(n.payloadVariant.value), n.payloadVariant.value.position && e.events.onPositionPacket.dispatch({
						id: n.id,
						rxTime: /* @__PURE__ */ new Date(),
						from: n.payloadVariant.value.num,
						to: n.payloadVariant.value.num,
						type: "direct",
						channel: sc.Primary,
						data: n.payloadVariant.value.position
					}), n.payloadVariant.value.user && e.events.onUserPacket.dispatch({
						id: n.id,
						rxTime: /* @__PURE__ */ new Date(),
						from: n.payloadVariant.value.num,
						to: n.payloadVariant.value.num,
						type: "direct",
						channel: sc.Primary,
						data: n.payloadVariant.value.user
					});
					break;
				case "config":
					n.payloadVariant.value.payloadVariant.case ? e.log.trace(W[W.HandleFromRadio], `💾 Received Config packet of variant: ${n.payloadVariant.value.payloadVariant.case}`) : e.log.warn(W[W.HandleFromRadio], "⚠️ Received Config packet of variant: UNK"), e.events.onConfigPacket.dispatch(n.payloadVariant.value);
					break;
				case "logRecord":
					e.log.trace(W[W.HandleFromRadio], "Received onLogRecord"), e.events.onLogRecord.dispatch(n.payloadVariant.value);
					break;
				case "configCompleteId":
					n.payloadVariant.value !== e.configId && e.log.error(W[W.HandleFromRadio], `❌ Invalid config id received from device, expected ${e.configId} but received ${n.payloadVariant.value}`), e.log.info(W[W.HandleFromRadio], `⚙️ Valid config id received from device: ${e.configId}`), e.updateDeviceStatus(U.DeviceConfigured);
					break;
				case "rebooted":
					e.configure().catch(() => {});
					break;
				case "moduleConfig":
					n.payloadVariant.value.payloadVariant.case ? e.log.trace(W[W.HandleFromRadio], `💾 Received Module Config packet of variant: ${n.payloadVariant.value.payloadVariant.case}`) : e.log.warn(W[W.HandleFromRadio], "⚠️ Received Module Config packet of variant: UNK"), e.events.onModuleConfigPacket.dispatch(n.payloadVariant.value);
					break;
				case "channel":
					e.log.trace(W[W.HandleFromRadio], `🔐 Received Channel: ${n.payloadVariant.value.index}`), e.events.onChannelPacket.dispatch(n.payloadVariant.value);
					break;
				case "queueStatus":
					e.log.trace(W[W.HandleFromRadio], `🚧 Received Queue Status: ${n.payloadVariant.value}`), e.events.onQueueStatus.dispatch(n.payloadVariant.value);
					break;
				case "xmodemPacket":
					e.xModem.handlePacket(n.payloadVariant.value);
					break;
				case "metadata":
					Number.parseFloat(n.payloadVariant.value.firmwareVersion) < Hs.minFwVer && e.log.fatal(W[W.HandleFromRadio], `Device firmware outdated. Min supported: ${Hs.minFwVer} got : ${n.payloadVariant.value.firmwareVersion}`), e.log.debug(W[W.GetMetadata], "🏷️ Received metadata packet"), e.events.onDeviceMetadataPacket.dispatch({
						id: n.id,
						rxTime: /* @__PURE__ */ new Date(),
						from: 0,
						to: 0,
						type: "direct",
						channel: sc.Primary,
						data: n.payloadVariant.value
					});
					break;
				case "mqttClientProxyMessage": break;
				case "clientNotification":
					e.log.trace(W[W.HandleFromRadio], `📣 Received ClientNotification: ${n.payloadVariant.value.message}`), e.events.onClientNotificationPacket.dispatch(n.payloadVariant.value);
					break;
				default: e.log.warn(W[W.HandleFromRadio], `⚠️ Unhandled payload variant: ${n.payloadVariant.case}`);
			}
		}
	}
} }), jc = class {
	constructor(e, t) {
		this.log = new ac({
			name: "iMeshDevice",
			prettyLogTemplate: "{{hh}}:{{MM}}:{{ss}}:{{ms}}	{{logLevelName}}	[{{name}}]	"
		}), this.transport = e, this.deviceStatus = U.DeviceDisconnected, this.isConfigured = !1, this.pendingSettingsChanges = !1, this.myNodeInfo = S(I.MyNodeInfoSchema), this.configId = t ?? this.generateRandId(), this.queue = new Tc(), this.events = new Cc(), this.xModem = new Oc(this.sendRaw.bind(this)), this.events.onDeviceStatus.subscribe((e) => {
			this.deviceStatus = e, e === U.DeviceConfigured ? this.isConfigured = !0 : e === U.DeviceConfiguring ? this.isConfigured = !1 : e === U.DeviceDisconnected && (this._heartbeatIntervalId !== void 0 && clearInterval(this._heartbeatIntervalId), this.complete());
		}), this.events.onMyNodeInfo.subscribe((e) => {
			this.myNodeInfo = e;
		}), this.events.onPendingSettingsChange.subscribe((e) => {
			this.pendingSettingsChanges = e;
		}), this.transport.fromDevice.pipeTo(Ac(this));
	}
	async sendText(e, t, n, r, i, a) {
		this.log.debug(W[W.SendText], `📤 Sending message to ${t ?? "broadcast"} on channel ${r?.toString() ?? 0}`);
		let o = new TextEncoder();
		return await this.sendPacket(o.encode(e), N.PortNum.TEXT_MESSAGE_APP, t ?? "broadcast", r, n, !1, !0, i, a);
	}
	sendWaypoint(e, t, n) {
		return this.log.debug(W[W.SendWaypoint], `📤 Sending waypoint to ${t} on channel ${n?.toString() ?? 0}`), e.id = this.generateRandId(), this.sendPacket(C(I.WaypointSchema, e), N.PortNum.WAYPOINT_APP, t, n, !0, !1);
	}
	async sendPacket(e, t, n, r = sc.Primary, i = !0, a = !0, o = !1, s, c) {
		this.log.trace(W[W.SendPacket], `📤 Sending ${N.PortNum[t]} to ${n}`);
		let l = S(I.MeshPacketSchema, {
			payloadVariant: {
				case: "decoded",
				value: {
					payload: e,
					portnum: t,
					wantResponse: a,
					emoji: c,
					replyId: s,
					dest: 0,
					requestId: 0,
					source: 0
				}
			},
			from: this.myNodeInfo.myNodeNum,
			to: n === "broadcast" ? Hs.broadcastNum : n === "self" ? this.myNodeInfo.myNodeNum : n,
			id: this.generateRandId(),
			wantAck: i,
			channel: r
		}), u = S(I.ToRadioSchema, { payloadVariant: {
			case: "packet",
			value: l
		} });
		return o && (l.rxTime = Math.trunc(Date.now() / 1e3), this.handleMeshPacket(l)), await this.sendRaw(C(I.ToRadioSchema, u), l.id);
	}
	async sendRaw(e, t = this.generateRandId()) {
		if (e.length > 512) throw Error("Message longer than 512 bytes, it will not be sent!");
		return this.queue.push({
			id: t,
			data: e
		}), await this.queue.processQueue(this.transport.toDevice), this.queue.wait(t);
	}
	async setConfig(e) {
		this.log.debug(W[W.SetConfig], `⚙️ Setting config, Variant: ${e.payloadVariant.case ?? "Unknown"}`), this.pendingSettingsChanges || await this.beginEditSettings();
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setConfig",
			value: e
		} });
		return this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async setModuleConfig(e) {
		this.log.debug(W[W.SetModuleConfig], "⚙️ Setting module config");
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setModuleConfig",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async setCannedMessages(e) {
		this.log.debug(W[W.SetCannedMessages], "⚙️ Setting CannedMessages");
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setCannedMessageModuleMessages",
			value: e.messages
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async setOwner(e) {
		this.log.debug(W[W.SetOwner], "👤 Setting owner");
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setOwner",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async setChannel(e) {
		this.log.debug(W[W.SetChannel], `📻 Setting Channel: ${e.index}`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setChannel",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async enterDfuMode() {
		this.log.debug(W[W.EnterDfuMode], "🔌 Entering DFU mode");
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "enterDfuModeRequest",
			value: !0
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	async setPosition(e) {
		return await this.sendPacket(C(I.PositionSchema, e), N.PortNum.POSITION_APP, "self");
	}
	async setFixedPosition(e, t) {
		let n = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setFixedPosition",
			value: S(I.PositionSchema, {
				latitudeI: Math.floor(e / 1e-7),
				longitudeI: Math.floor(t / 1e-7)
			})
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, n), N.PortNum.ADMIN_APP, "self", 0, !0, !1);
	}
	async removeFixedPosition() {
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "removeFixedPosition",
			value: !0
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self", 0, !0, !1);
	}
	async getChannel(e) {
		this.log.debug(W[W.GetChannel], `📻 Requesting Channel: ${e}`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "getChannelRequest",
			value: e + 1
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async getConfig(e) {
		this.log.debug(W[W.GetConfig], "⚙️ Requesting config");
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "getConfigRequest",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async getModuleConfig(e) {
		this.log.debug(W[W.GetModuleConfig], "⚙️ Requesting module config");
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "getModuleConfigRequest",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async getOwner() {
		this.log.debug(W[W.GetOwner], "👤 Requesting owner");
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "getOwnerRequest",
			value: !0
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	async getMetadata(e) {
		this.log.debug(W[W.GetMetadata], `🏷️ Requesting metadata from ${e}`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "getDeviceMetadataRequest",
			value: !0
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, e, sc.Admin);
	}
	async clearChannel(e) {
		this.log.debug(W[W.ClearChannel], `📻 Clearing Channel ${e}`);
		let t = S(Nr.ChannelSchema, {
			index: e,
			role: Nr.Channel_Role.DISABLED
		}), n = S(R.AdminMessageSchema, { payloadVariant: {
			case: "setChannel",
			value: t
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, n), N.PortNum.ADMIN_APP, "self");
	}
	async beginEditSettings() {
		this.events.onPendingSettingsChange.dispatch(!0);
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "beginEditSettings",
			value: !0
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	async commitEditSettings() {
		this.events.onPendingSettingsChange.dispatch(!1);
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "commitEditSettings",
			value: !0
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	async resetNodes() {
		this.log.debug(W[W.ResetNodes], "📻 Resetting NodeDB");
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "nodedbReset",
			value: 1
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	async removeNodeByNum(e) {
		this.log.debug(W[W.RemoveNodeByNum], `📻 Removing Node ${e} from NodeDB`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "removeByNodenum",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async shutdown(e) {
		this.log.debug(W[W.Shutdown], `🔌 Shutting down ${e > 2 ? "now" : `in ${e} seconds`}`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "shutdownSeconds",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async reboot(e) {
		this.log.debug(W[W.Reboot], `🔌 Rebooting node ${e === 0 ? "now" : `in ${e} seconds`}`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "rebootSeconds",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async rebootOta(e) {
		this.log.debug(W[W.RebootOta], `🔌 Rebooting into OTA mode ${e === 0 ? "now" : `in ${e} seconds`}`);
		let t = S(R.AdminMessageSchema, { payloadVariant: {
			case: "rebootOtaSeconds",
			value: e
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, t), N.PortNum.ADMIN_APP, "self");
	}
	async factoryResetDevice() {
		this.log.debug(W[W.FactoryReset], "♻️ Factory resetting device");
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "factoryResetDevice",
			value: 1
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	async factoryResetConfig() {
		this.log.debug(W[W.FactoryReset], "♻️ Factory resetting config");
		let e = S(R.AdminMessageSchema, { payloadVariant: {
			case: "factoryResetConfig",
			value: 1
		} });
		return await this.sendPacket(C(R.AdminMessageSchema, e), N.PortNum.ADMIN_APP, "self");
	}
	configure() {
		this.log.debug(W[W.Configure], "⚙️ Requesting device configuration"), this.updateDeviceStatus(U.DeviceConfiguring);
		let e = S(I.ToRadioSchema, { payloadVariant: {
			case: "wantConfigId",
			value: this.configId
		} });
		return this.sendRaw(C(I.ToRadioSchema, e)).catch((e) => {
			throw this.deviceStatus === U.DeviceDisconnected ? Error("Device connection lost") : e;
		});
	}
	heartbeat() {
		this.log.debug(W[W.Ping], "❤️ Send heartbeat ping to radio");
		let e = S(I.ToRadioSchema, { payloadVariant: {
			case: "heartbeat",
			value: {}
		} });
		return this.sendRaw(C(I.ToRadioSchema, e));
	}
	setHeartbeatInterval(e) {
		this._heartbeatIntervalId !== void 0 && clearInterval(this._heartbeatIntervalId), this._heartbeatIntervalId = setInterval(() => {
			this.heartbeat().catch((e) => {
				this.log.error(W[W.Ping], `⚠️ Unable to send heartbeat: ${e.message}`);
			});
		}, e);
	}
	async traceRoute(e) {
		let t = S(I.RouteDiscoverySchema, { route: [] });
		return await this.sendPacket(C(I.RouteDiscoverySchema, t), N.PortNum.TRACEROUTE_APP, e);
	}
	async requestPosition(e) {
		return await this.sendPacket(/* @__PURE__ */ new Uint8Array(), N.PortNum.POSITION_APP, e);
	}
	updateDeviceStatus(e) {
		e !== this.deviceStatus && this.events.onDeviceStatus.dispatch(e);
	}
	generateRandId() {
		let e = crypto.getRandomValues(/* @__PURE__ */ new Uint32Array(1));
		if (!e[0]) throw Error("Cannot generate CSPRN");
		return Math.floor(e[0] * 2 ** -32 * 1e9);
	}
	complete() {
		this.queue.clear();
	}
	async disconnect() {
		this.log.debug(W[W.Disconnect], "🔌 Disconnecting from device"), this._heartbeatIntervalId !== void 0 && clearInterval(this._heartbeatIntervalId), this.complete(), await this.transport.toDevice.close(), await this.transport.disconnect();
	}
	handleMeshPacket(e) {
		switch (this.events.onMeshPacket.dispatch(e), e.from !== this.myNodeInfo.myNodeNum && this.events.onMeshHeartbeat.dispatch(/* @__PURE__ */ new Date()), e.payloadVariant.case) {
			case "decoded":
				this.handleDecodedPacket(e.payloadVariant.value, e);
				break;
			case "encrypted":
				this.log.debug(W[W.HandleMeshPacket], "🔐 Device received encrypted data packet, ignoring.");
				break;
			default: throw Error(`Unhandled case ${e.payloadVariant.case}`);
		}
	}
	handleDecodedPacket(e, t) {
		let n, r, i = {
			id: t.id,
			rxTime: /* @__PURE__ */ new Date(t.rxTime * 1e3),
			type: t.to === Hs.broadcastNum ? "broadcast" : "direct",
			from: t.from,
			to: t.to,
			channel: t.channel
		};
		switch (this.log.trace(W[W.HandleMeshPacket], `📦 Received ${N.PortNum[e.portnum]} packet`), e.portnum) {
			case N.PortNum.TEXT_MESSAGE_APP:
				this.events.onMessagePacket.dispatch({
					...i,
					data: new TextDecoder().decode(e.payload)
				});
				break;
			case N.PortNum.REMOTE_HARDWARE_APP:
				this.events.onRemoteHardwarePacket.dispatch({
					...i,
					data: T(Es.HardwareMessageSchema, e.payload)
				});
				break;
			case N.PortNum.POSITION_APP:
				this.events.onPositionPacket.dispatch({
					...i,
					data: T(I.PositionSchema, e.payload)
				});
				break;
			case N.PortNum.NODEINFO_APP:
				this.events.onUserPacket.dispatch({
					...i,
					data: T(I.UserSchema, e.payload)
				});
				break;
			case N.PortNum.ROUTING_APP:
				switch (r = T(I.RoutingSchema, e.payload), this.events.onRoutingPacket.dispatch({
					...i,
					data: r
				}), r.variant.case) {
					case "errorReason":
						r.variant.value === I.Routing_Error.NONE ? this.queue.processAck(e.requestId) : this.queue.processError({
							id: e.requestId,
							error: r.variant.value
						});
						break;
					case "routeReply": break;
					case "routeRequest": break;
					default: throw Error(`Unhandled case ${r.variant.case}`);
				}
				break;
			case N.PortNum.ADMIN_APP:
				switch (n = T(R.AdminMessageSchema, e.payload), n.payloadVariant.case) {
					case "getChannelResponse":
						this.events.onChannelPacket.dispatch(n.payloadVariant.value);
						break;
					case "getOwnerResponse":
						this.events.onUserPacket.dispatch({
							...i,
							data: n.payloadVariant.value
						});
						break;
					case "getConfigResponse":
						this.events.onConfigPacket.dispatch(n.payloadVariant.value);
						break;
					case "getModuleConfigResponse":
						this.events.onModuleConfigPacket.dispatch(n.payloadVariant.value);
						break;
					case "getDeviceMetadataResponse":
						this.log.debug(W[W.GetMetadata], `🏷️ Received metadata packet from ${e.source}`), this.events.onDeviceMetadataPacket.dispatch({
							...i,
							data: n.payloadVariant.value
						});
						break;
					case "getCannedMessageModuleMessagesResponse":
						this.log.debug(W[W.GetMetadata], "🥫 Received CannedMessage Module Messages response packet"), this.events.onCannedMessageModulePacket.dispatch({
							...i,
							data: n.payloadVariant.value
						});
						break;
					default: this.log.error(W[W.HandleMeshPacket], `⚠️ Received unhandled AdminMessage, type ${n.payloadVariant.case ?? "undefined"}`, e.payload);
				}
				break;
			case N.PortNum.WAYPOINT_APP:
				this.events.onWaypointPacket.dispatch({
					...i,
					data: T(I.WaypointSchema, e.payload)
				});
				break;
			case N.PortNum.AUDIO_APP:
				this.events.onAudioPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.DETECTION_SENSOR_APP:
				this.events.onDetectionSensorPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.REPLY_APP:
				this.events.onPingPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.IP_TUNNEL_APP:
				this.events.onIpTunnelPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.PAXCOUNTER_APP:
				this.events.onPaxcounterPacket.dispatch({
					...i,
					data: T(hs.PaxcountSchema, e.payload)
				});
				break;
			case N.PortNum.SERIAL_APP:
				this.events.onSerialPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.STORE_FORWARD_APP:
				this.events.onStoreForwardPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.RANGE_TEST_APP:
				this.events.onRangeTestPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.TELEMETRY_APP:
				this.events.onTelemetryPacket.dispatch({
					...i,
					data: T(pa.TelemetrySchema, e.payload)
				});
				break;
			case N.PortNum.ZPS_APP:
				this.events.onZpsPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.SIMULATOR_APP:
				this.events.onSimulatorPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.TRACEROUTE_APP:
				this.events.onTraceRoutePacket.dispatch({
					...i,
					data: T(I.RouteDiscoverySchema, e.payload)
				});
				break;
			case N.PortNum.NEIGHBORINFO_APP:
				this.events.onNeighborInfoPacket.dispatch({
					...i,
					data: T(I.NeighborInfoSchema, e.payload)
				});
				break;
			case N.PortNum.ATAK_PLUGIN:
				this.events.onAtakPluginPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.MAP_REPORT_APP:
				this.events.onMapReportPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.PRIVATE_APP:
				this.events.onPrivatePacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case N.PortNum.ATAK_FORWARDER:
				this.events.onAtakForwarderPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			default: throw Error(`Unhandled case ${e.portnum}`);
		}
	}
}, Mc = class e {
	static async create(t) {
		let n = await navigator.serial.requestPort();
		return await n.open({ baudRate: t || 115200 }), new e(n);
	}
	static async createFromPort(t, n) {
		return (!t.readable || !t.writable) && await t.open({ baudRate: n || 115200 }), new e(t);
	}
	constructor(e) {
		if (this.pipePromise = null, this.lastStatus = H.DeviceStatusEnum.DeviceDisconnected, this.closingByUser = !1, !e.readable || !e.writable) throw Error("Stream not accessible");
		this.connection = e, this.portReadable = e.readable, this.abortController = new AbortController();
		let t = this.abortController, n = kc.toDeviceStream();
		this.pipePromise = n.readable.pipeTo(e.writable, { signal: this.abortController.signal }).catch((e) => {
			t.signal.aborted || (console.error("Error piping data to serial port:", e), this.connection.close().catch(() => {}), this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "write-error"));
		}), this._toDevice = n.writable, this._fromDevice = new ReadableStream({ start: async (e) => {
			this.fromDeviceController = e, this.emitStatus(H.DeviceStatusEnum.DeviceConnecting);
			let t = this.portReadable.pipeThrough(kc.fromDeviceStream()), n = t.getReader(), r = (e) => {
				let { port: t } = e;
				t && t === this.connection && this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "serial-disconnected");
			};
			navigator.serial.addEventListener("disconnect", r), this.emitStatus(H.DeviceStatusEnum.DeviceConnected);
			try {
				for (;;) {
					let { value: t, done: r } = await n.read();
					if (r) break;
					e.enqueue(t);
				}
				e.close();
			} catch (n) {
				this.closingByUser || this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "read-error"), e.error(n instanceof Error ? n : Error(String(n)));
				try {
					await t.cancel();
				} catch {}
			} finally {
				n.releaseLock(), navigator.serial.removeEventListener("disconnect", r);
			}
		} });
	}
	get toDevice() {
		return this._toDevice;
	}
	get fromDevice() {
		return this._fromDevice;
	}
	emitStatus(e, t) {
		e !== this.lastStatus && (this.lastStatus = e, this.fromDeviceController?.enqueue({
			type: "status",
			data: {
				status: e,
				reason: t
			}
		}));
	}
	async disconnect() {
		try {
			if (this.closingByUser = !0, this.abortController.abort(), this.pipePromise && await this.pipePromise, this._fromDevice?.locked) try {
				await this._fromDevice.cancel();
			} catch {}
			await this.connection.close();
		} catch (e) {
			console.warn("Could not cleanly disconnect serial port:", e);
		} finally {
			this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "user"), this.closingByUser = !1;
		}
	}
	async reconnect() {
		this.emitStatus(H.DeviceStatusEnum.DeviceConnecting, "reconnect");
		try {
			if (!this.connection.readable || !this.connection.writable) throw Error("Stream not accessible");
			this.portReadable = this.connection.readable, this.abortController = new AbortController();
			let e = this.abortController;
			this.pipePromise = kc.toDeviceStream().readable.pipeTo(this.connection.writable, { signal: this.abortController.signal }).catch((t) => {
				e.signal.aborted || (console.error("Error piping data to serial port (reconnect):", t), this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "write-error"));
			}), this.emitStatus(H.DeviceStatusEnum.DeviceConnected, "reconnected");
		} catch (e) {
			throw this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "reconnect-failed"), e;
		}
	}
};
//#endregion
//#region node_modules/@meshtastic/transport-web-bluetooth/dist/mod.js
function Nc(e) {
	return e.buffer instanceof ArrayBuffer && e.byteOffset === 0 && e.byteLength === e.buffer.byteLength ? e.buffer : e.slice().buffer;
}
var Pc = class e {
	static {
		this.ToRadioUuid = "f75c76d2-129e-4dad-a1dd-7866124401e7";
	}
	static {
		this.FromRadioUuid = "2c55e69e-4993-11ed-b878-0242ac120002";
	}
	static {
		this.FromNumUuid = "ed9da18c-a800-4f66-a670-aa7547e34453";
	}
	static {
		this.ServiceUuid = "6ba1b218-15a8-461f-9fa8-5dcae273eafd";
	}
	static async create() {
		let t = await navigator.bluetooth.requestDevice({ filters: [{ services: [e.ServiceUuid] }] });
		return await e.prepareConnection(t);
	}
	static async createFromDevice(t) {
		return await e.prepareConnection(t);
	}
	static async prepareConnection(t) {
		let n = await t.gatt?.connect();
		if (!n) throw Error("Failed to connect to GATT server");
		let r = await n.getPrimaryService(e.ServiceUuid), i = await r.getCharacteristic(e.ToRadioUuid), a = await r.getCharacteristic(e.FromRadioUuid), o = await r.getCharacteristic(e.FromNumUuid);
		if (!i || !a || !o) throw Error("Failed to find required characteristics");
		return new e(i, a, o, n);
	}
	constructor(e, t, n, r) {
		this.lastStatus = H.DeviceStatusEnum.DeviceDisconnected, this.closingByUser = !1, this.reading = !1, this.onGattDisconnected = () => {
			this.closingByUser || this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "gatt-disconnected");
		}, this.onFromNumChanged = () => {
			this.readFromRadio();
		}, this.toRadioCharacteristic = e, this.fromRadioCharacteristic = t, this.fromNumCharacteristic = n, this.gattServer = r, this._fromDevice = new ReadableStream({ start: async (e) => {
			this.fromDeviceController = e, this.emitStatus(H.DeviceStatusEnum.DeviceConnecting), this.gattServer.device.addEventListener("gattserverdisconnected", this.onGattDisconnected);
			try {
				await this.fromNumCharacteristic.startNotifications(), this.fromNumCharacteristic.addEventListener("characteristicvaluechanged", this.onFromNumChanged), this.emitStatus(H.DeviceStatusEnum.DeviceConnected), this.readFromRadio();
			} catch {
				this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "notify-failed"), this.gattServer.device.removeEventListener("gattserverdisconnected", this.onGattDisconnected);
			}
		} }), this._toDevice = new WritableStream({ write: async (e) => {
			try {
				let t = Nc(e);
				await this.toRadioCharacteristic.writeValue(t), this.readFromRadio();
			} catch (e) {
				throw this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "write-error"), e;
			}
		} });
	}
	get toDevice() {
		return this._toDevice;
	}
	get fromDevice() {
		return this._fromDevice;
	}
	disconnect() {
		try {
			this.closingByUser = !0, this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "user");
			try {
				this.fromNumCharacteristic.stopNotifications?.();
			} catch {}
			this.fromNumCharacteristic.removeEventListener("characteristicvaluechanged", this.onFromNumChanged), this.gattServer.device.removeEventListener("gattserverdisconnected", this.onGattDisconnected), this.gattServer.disconnect();
		} finally {
			this.closingByUser = !1;
		}
		return Promise.resolve();
	}
	async readFromRadio() {
		if (!this.reading) {
			this.reading = !0;
			try {
				let e = !0;
				for (; e && this.fromRadioCharacteristic;) {
					let t = await this.fromRadioCharacteristic.readValue();
					t.byteLength === 0 ? e = !1 : this.enqueue({
						type: "packet",
						data: new Uint8Array(t.buffer)
					});
				}
			} catch (e) {
				throw this.closingByUser || this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "read-error"), e;
			} finally {
				this.reading = !1;
			}
		}
	}
	emitStatus(e, t) {
		e !== this.lastStatus && (this.lastStatus = e, this.fromDeviceController?.enqueue({
			type: "status",
			data: {
				status: e,
				reason: t
			}
		}));
	}
	enqueue(e) {
		this.fromDeviceController?.enqueue(e);
	}
}, Fc = 3e3, Ic = 7e3, Lc = 4e3;
function Rc(e) {
	return e.buffer instanceof ArrayBuffer && e.byteOffset === 0 && e.byteLength === e.buffer.byteLength ? e.buffer : e.slice().buffer;
}
var zc = class e {
	static async create(t, n) {
		let r = `${n ? "https" : "http"}://${t}`;
		return await fetch(`${r}/api/v1/toradio`, { method: "OPTIONS" }), new e(r);
	}
	constructor(e) {
		this.lastStatus = H.DeviceStatusEnum.DeviceDisconnected, this.closingByUser = !1, this.url = e, this.receiveBatchRequests = !1, this.fetchInterval = Fc, this.fetching = !1, this._toDevice = new WritableStream({ write: async (e) => {
			try {
				await this.writeToRadio(e);
			} catch (e) {
				if (!this.closingByUser) {
					this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, this.isTimeoutOrAbort(e) ? "write-timeout" : "write-error");
					return;
				}
				throw e;
			}
		} }), this._fromDevice = new ReadableStream({
			start: (e) => {
				this.fromDeviceController = e, this.emitStatus(H.DeviceStatusEnum.DeviceConnecting), this.safePoll(), this.interval = setInterval(() => void this.safePoll(), this.fetchInterval);
			},
			cancel: () => {
				this.interval && clearInterval(this.interval), this.interval = void 0;
			}
		});
	}
	async readFromRadio() {
		let e = /* @__PURE__ */ new ArrayBuffer(1);
		for (; e.byteLength > 0;) {
			let t = new AbortController();
			this.inflightReadController = t;
			let n = AbortSignal.any([t.signal, AbortSignal.timeout(Ic)]);
			try {
				let t = await fetch(`${this.url}/api/v1/fromradio?all=${this.receiveBatchRequests ? "true" : "false"}`, {
					method: "GET",
					headers: { Accept: "application/x-protobuf" },
					signal: n
				});
				if (!t.ok) throw Error(`fromradio ${t.status} ${t.statusText}`);
				this.emitStatus(H.DeviceStatusEnum.DeviceConnected), e = await t.arrayBuffer(), e.byteLength > 0 && this.fromDeviceController?.enqueue({
					type: "packet",
					data: new Uint8Array(e)
				});
			} finally {
				this.inflightReadController = void 0;
			}
		}
	}
	async writeToRadio(e) {
		try {
			let t = await fetch(`${this.url}/api/v1/toradio`, {
				method: "PUT",
				headers: { "Content-Type": "application/x-protobuf" },
				body: Rc(e),
				signal: AbortSignal.timeout(Lc)
			});
			if (!t.ok) throw Error(`toradio ${t.status} ${t.statusText}`);
		} catch (e) {
			if (!this.closingByUser) {
				this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, this.isTimeoutOrAbort(e) ? "write-timeout" : "write-error");
				return;
			}
			throw e;
		}
	}
	get toDevice() {
		return this._toDevice;
	}
	get fromDevice() {
		return this._fromDevice;
	}
	disconnect() {
		this.closingByUser = !0, this.interval && clearInterval(this.interval), this.interval = void 0, this.fetching = !1;
		try {
			this.inflightReadController?.abort();
		} catch {}
		return this.inflightReadController = void 0, this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, "user"), Promise.resolve();
	}
	emitStatus(e, t) {
		e !== this.lastStatus && (this.lastStatus = e, this.fromDeviceController?.enqueue({
			type: "status",
			data: {
				status: e,
				reason: t
			}
		}));
	}
	isTimeoutOrAbort(e) {
		return e instanceof DOMException && (e.name === "AbortError" || e.name === "TimeoutError") || e instanceof Error && (e.name === "AbortError" || e.name === "TimeoutError");
	}
	async safePoll() {
		if (!this.fetching) {
			this.fetching = !0;
			try {
				await this.readFromRadio();
			} catch (e) {
				this.closingByUser || this.emitStatus(H.DeviceStatusEnum.DeviceDisconnected, this.isTimeoutOrAbort(e) ? "read-timeout" : "read-error");
			} finally {
				this.fetching = !1;
			}
		}
	}
}, K = Symbol("NOT_RESOLVED");
function q(e, t) {
	return {
		tagName: e,
		nodeKind: "scalar",
		implicit: t.implicit ?? !1,
		matchByTagPrefix: t.matchByTagPrefix ?? !1,
		implicitFirstChars: t.implicitFirstChars ?? null,
		resolve: t.resolve,
		identify: t.identify,
		represent: t.represent ?? ((e) => String(e)),
		representTagName: t.representTagName ?? (() => e)
	};
}
function Bc(e, t) {
	let n = t.finalize === void 0;
	return {
		tagName: e,
		nodeKind: "sequence",
		implicit: !1,
		matchByTagPrefix: t.matchByTagPrefix ?? !1,
		create: t.create,
		addItem: t.addItem,
		finalize: t.finalize ?? ((e) => e),
		carrierIsResult: n,
		identify: t.identify,
		represent: t.represent ?? ((e) => e),
		representTagName: t.representTagName ?? (() => e)
	};
}
function Vc(e, t) {
	let n = t.finalize === void 0;
	return {
		tagName: e,
		nodeKind: "mapping",
		implicit: !1,
		matchByTagPrefix: t.matchByTagPrefix ?? !1,
		create: t.create,
		addPair: t.addPair,
		has: t.has,
		keys: t.keys,
		get: t.get,
		finalize: t.finalize ?? ((e) => e),
		carrierIsResult: n,
		identify: t.identify,
		represent: t.represent ?? ((e) => e),
		representTagName: t.representTagName ?? (() => e)
	};
}
var Hc = q("tag:yaml.org,2002:str", {
	resolve: (e) => e,
	identify: (e) => typeof e == "string"
}), Uc = [
	"",
	"~",
	"null",
	"Null",
	"NULL"
], Wc = q("tag:yaml.org,2002:null", {
	implicit: !0,
	implicitFirstChars: [
		"",
		"~",
		"n",
		"N"
	],
	resolve: (e) => Uc.indexOf(e) === -1 ? K : null,
	identify: (e) => e === null,
	represent: () => "null"
}), Gc = q("tag:yaml.org,2002:null", {
	implicit: !0,
	implicitFirstChars: ["n"],
	resolve: (e, t) => e === "null" || t && e === "" ? null : K,
	identify: (e) => e === null,
	represent: () => "null"
}), Kc = [
	"",
	"~",
	"null",
	"Null",
	"NULL"
], qc = q("tag:yaml.org,2002:null", {
	implicit: !0,
	implicitFirstChars: [
		"",
		"~",
		"n",
		"N"
	],
	resolve: (e) => Kc.indexOf(e) === -1 ? K : null,
	identify: (e) => e === null,
	represent: () => "null"
}), Jc = [
	"true",
	"True",
	"TRUE"
], Yc = [
	"false",
	"False",
	"FALSE"
], Xc = q("tag:yaml.org,2002:bool", {
	implicit: !0,
	implicitFirstChars: [
		"t",
		"T",
		"f",
		"F"
	],
	resolve: (e) => Jc.indexOf(e) !== -1 || Yc.indexOf(e) === -1 && K,
	identify: (e) => Object.prototype.toString.call(e) === "[object Boolean]",
	represent: (e) => e ? "true" : "false"
}), Zc = ["true"], Qc = ["false"], $c = q("tag:yaml.org,2002:bool", {
	implicit: !0,
	implicitFirstChars: ["t", "f"],
	resolve: (e) => Zc.indexOf(e) !== -1 || Qc.indexOf(e) === -1 && K,
	identify: (e) => Object.prototype.toString.call(e) === "[object Boolean]",
	represent: (e) => e ? "true" : "false"
}), el = [
	"true",
	"True",
	"TRUE",
	"y",
	"Y",
	"yes",
	"Yes",
	"YES",
	"on",
	"On",
	"ON"
], tl = [
	"false",
	"False",
	"FALSE",
	"n",
	"N",
	"no",
	"No",
	"NO",
	"off",
	"Off",
	"OFF"
], nl = q("tag:yaml.org,2002:bool", {
	implicit: !0,
	implicitFirstChars: [
		"y",
		"Y",
		"n",
		"N",
		"t",
		"T",
		"f",
		"F",
		"o",
		"O"
	],
	resolve: (e) => el.indexOf(e) !== -1 || tl.indexOf(e) === -1 && K,
	identify: (e) => Object.prototype.toString.call(e) === "[object Boolean]",
	represent: (e) => e ? "true" : "false"
}), rl = /* @__PURE__ */ RegExp("^(?:0o[0-7]+|0x[0-9a-fA-F]+|[-+]?[0-9]+)$"), il = /* @__PURE__ */ RegExp("^(?:[-+]?0b[0-1]+|[-+]?0o[0-7]+|[-+]?0x[0-9a-fA-F]+|[-+]?[0-9]+)$");
function al(e) {
	let t = e, n = 1;
	return (t[0] === "-" || t[0] === "+") && (t[0] === "-" && (n = -1), t = t.slice(1)), t.startsWith("0b") ? n * parseInt(t.slice(2), 2) : t.startsWith("0o") ? n * parseInt(t.slice(2), 8) : t.startsWith("0x") ? n * parseInt(t.slice(2), 16) : n * parseInt(t, 10);
}
function ol(e, t) {
	if (t) {
		if (!il.test(e)) return K;
	} else if (!rl.test(e)) return K;
	let n = al(e);
	return Number.isFinite(n) ? n : K;
}
var sl = q("tag:yaml.org,2002:int", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		..."0123456789"
	],
	resolve: ol,
	identify: (e) => Number.isInteger(e) && !Object.is(e, -0) && e.toString(10).indexOf("e") < 0,
	represent: (e) => e.toString(10)
}), cl = /* @__PURE__ */ RegExp("^-?(?:0|[1-9][0-9]*)$"), ll = /* @__PURE__ */ RegExp("^(?:[-+]?0b[0-1]+|[-+]?0o[0-7]+|[-+]?0x[0-9a-fA-F]+|[-+]?[0-9]+)$");
function ul(e) {
	let t = e, n = 1;
	return (t[0] === "-" || t[0] === "+") && (t[0] === "-" && (n = -1), t = t.slice(1)), t.startsWith("0b") ? n * parseInt(t.slice(2), 2) : t.startsWith("0o") ? n * parseInt(t.slice(2), 8) : t.startsWith("0x") ? n * parseInt(t.slice(2), 16) : n * parseInt(t, 10);
}
function dl(e, t) {
	if (t) {
		if (!ll.test(e)) return K;
	} else if (!cl.test(e)) return K;
	let n = ul(e);
	return Number.isFinite(n) ? n : K;
}
var fl = q("tag:yaml.org,2002:int", {
	implicit: !0,
	implicitFirstChars: ["-", ..."0123456789"],
	resolve: dl,
	identify: (e) => Number.isInteger(e) && !Object.is(e, -0) && e.toString(10).indexOf("e") < 0,
	represent: (e) => e.toString(10)
}), pl = /* @__PURE__ */ RegExp("^(?:[-+]?0b[0-1_]+|[-+]?0[0-7_]+|[-+]?0x[0-9a-fA-F_]+|[-+]?[0-9][0-9_]*(?::[0-5]?[0-9])+|[-+]?(?:0|[1-9][0-9_]*))$");
function ml(e) {
	let t = e.replace(/_/g, ""), n = 1;
	if ((t[0] === "-" || t[0] === "+") && (t[0] === "-" && (n = -1), t = t.slice(1)), t.startsWith("0b")) return n * parseInt(t.slice(2), 2);
	if (t.startsWith("0x")) return n * parseInt(t.slice(2), 16);
	if (t.includes(":")) {
		let e = 0;
		for (let n of t.split(":")) e = e * 60 + Number(n);
		return n * e;
	}
	return t !== "0" && t[0] === "0" ? n * parseInt(t, 8) : n * parseInt(t, 10);
}
function hl(e) {
	if (!pl.test(e)) return K;
	let t = ml(e);
	return Number.isFinite(t) ? t : K;
}
var gl = q("tag:yaml.org,2002:int", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		..."0123456789"
	],
	resolve: hl,
	identify: (e) => Number.isInteger(e) && !Object.is(e, -0) && e.toString(10).indexOf("e") < 0,
	represent: (e) => e.toString(10)
}), _l = /* @__PURE__ */ RegExp("^(?:[-+]?[0-9]+(?:\\.[0-9]*)?(?:[eE][-+]?[0-9]+)?|[-+]?\\.[0-9]+(?:[eE][-+]?[0-9]+)?|[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$"), vl = /* @__PURE__ */ RegExp("^(?:[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$");
function yl(e) {
	if (!_l.test(e)) return K;
	let t = e.toLowerCase(), n = t[0] === "-" ? -1 : 1;
	if ("+-".includes(t[0]) && (t = t.slice(1)), t === ".inf") return n === 1 ? Infinity : -Infinity;
	if (t === ".nan") return NaN;
	let r = n * parseFloat(t);
	return Number.isFinite(r) || vl.test(e) ? r : K;
}
function bl(e) {
	if (isNaN(e)) return ".nan";
	if (e === Infinity) return ".inf";
	if (e === -Infinity) return "-.inf";
	if (Object.is(e, -0)) return "-0.0";
	let t = e.toString(10);
	return /^[-+]?[0-9]+e/.test(t) ? t.replace("e", ".e") : t;
}
var xl = q("tag:yaml.org,2002:float", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		".",
		..."0123456789"
	],
	resolve: yl,
	identify: (e) => typeof e == "number" && (!Number.isInteger(e) || Object.is(e, -0) || e.toString(10).indexOf("e") >= 0),
	represent: bl
}), Sl = /* @__PURE__ */ RegExp("^-?(?:0|[1-9][0-9]*)(?:\\.[0-9]*)?(?:[eE][-+]?[0-9]+)?$"), Cl = /* @__PURE__ */ RegExp("^(?:[-+]?[0-9]+(?:\\.[0-9]*)?(?:[eE][-+]?[0-9]+)?|[-+]?\\.[0-9]+(?:[eE][-+]?[0-9]+)?|[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$");
function wl(e, t) {
	if (t) {
		if (!Cl.test(e)) return K;
		let t = e.toLowerCase(), n = t[0] === "-" ? -1 : 1;
		if ("+-".includes(t[0]) && (t = t.slice(1)), t === ".inf") return n === 1 ? Infinity : -Infinity;
		if (t === ".nan") return NaN;
		let r = n * parseFloat(t);
		return Number.isFinite(r) ? r : K;
	}
	if (!Sl.test(e)) return K;
	let n = Number(e);
	return Number.isFinite(n) ? n : K;
}
function Tl(e) {
	if (isNaN(e)) return ".nan";
	if (e === Infinity) return ".inf";
	if (e === -Infinity) return "-.inf";
	if (Object.is(e, -0)) return "-0.0";
	let t = e.toString(10);
	return /^[-+]?[0-9]+e/.test(t) ? t.replace("e", ".e") : t;
}
var El = q("tag:yaml.org,2002:float", {
	implicit: !0,
	implicitFirstChars: ["-", ..."0123456789"],
	resolve: wl,
	identify: (e) => typeof e == "number" && (!Number.isInteger(e) || Object.is(e, -0) || e.toString(10).indexOf("e") >= 0),
	represent: Tl
}), Dl = /* @__PURE__ */ RegExp("^(?:[-+]?(?:(?:[0-9][0-9_]*)?\\.[0-9_]*)(?:[eE][-+][0-9]+)?|[-+]?[0-9][0-9_]*(?::[0-5]?[0-9])+\\.[0-9_]*|[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$"), Ol = /* @__PURE__ */ RegExp("^(?:[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$");
function kl(e) {
	if (!Dl.test(e)) return K;
	let t = e.toLowerCase().replace(/_/g, ""), n = t[0] === "-" ? -1 : 1;
	if ("+-".includes(t[0]) && (t = t.slice(1)), t === ".inf") return n === 1 ? Infinity : -Infinity;
	if (t === ".nan") return NaN;
	let r = 0;
	if (t.includes(":")) {
		for (let e of t.split(":")) r = r * 60 + Number(e);
		r *= n;
	} else r = n * parseFloat(t);
	return Number.isFinite(r) || Ol.test(e) ? r : K;
}
function Al(e) {
	if (isNaN(e)) return ".nan";
	if (e === Infinity) return ".inf";
	if (e === -Infinity) return "-.inf";
	if (Object.is(e, -0)) return "-0.0";
	let t = e.toString(10);
	return /^[-+]?[0-9]+e/.test(t) ? t.replace("e", ".e") : t;
}
var jl = q("tag:yaml.org,2002:float", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		".",
		..."0123456789"
	],
	resolve: kl,
	identify: (e) => typeof e == "number" && (!Number.isInteger(e) || Object.is(e, -0) || e.toString(10).indexOf("e") >= 0),
	represent: Al
}), Ml = q("tag:yaml.org,2002:merge", {
	implicit: !0,
	implicitFirstChars: ["<"],
	resolve: (e, t) => e === "<<" || t && e === "" ? "<<" : K,
	identify: () => !1
}), Nl = /^[A-Za-z0-9+/]*={0,2}$/;
function Pl(e) {
	let t = e.replace(/\s/g, "");
	if (t.length % 4 != 0 || !Nl.test(t)) return K;
	let n = atob(t), r = new Uint8Array(n.length);
	for (let e = 0; e < n.length; e++) r[e] = n.charCodeAt(e);
	return r;
}
function Fl(e) {
	let t = "";
	for (let n = 0; n < e.length; n++) t += String.fromCharCode(e[n]);
	return btoa(t);
}
var Il = q("tag:yaml.org,2002:binary", {
	resolve: Pl,
	identify: (e) => Object.prototype.toString.call(e) === "[object Uint8Array]",
	represent: Fl
}), Ll = /* @__PURE__ */ RegExp("^([0-9][0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9])$"), Rl = /* @__PURE__ */ RegExp("^([0-9][0-9][0-9][0-9])-([0-9][0-9]?)-([0-9][0-9]?)(?:[Tt]|[ \\t]+)([0-9][0-9]?):([0-9][0-9]):([0-9][0-9])(?:\\.([0-9]*))?(?:[ \\t]*(Z|([-+])([0-9][0-9]?)(?::([0-9][0-9]))?))?$");
function zl(e, t, n, r = 0, i = 0, a = 0, o = 0) {
	let s = new Date(Date.UTC(e, t, n, r, i, a, o));
	return s.setUTCFullYear(e, t, n), s;
}
function Bl(e) {
	let t = Ll.exec(e);
	if (t === null && (t = Rl.exec(e)), t === null) return K;
	let n = +t[1], r = t[2] - 1, i = +t[3];
	if (!t[4]) {
		let e = zl(n, r, i);
		return e.getUTCFullYear() !== n || e.getUTCMonth() !== r || e.getUTCDate() !== i ? K : e;
	}
	let a = +t[4], o = +t[5], s = +t[6], c = 0;
	if (a > 23 || o > 59 || s > 59) return K;
	if (t[7]) {
		let e = t[7].slice(0, 3);
		for (; e.length < 3;) e += "0";
		c = +e;
	}
	let l = zl(n, r, i, a, o, s, c);
	if (l.getUTCFullYear() !== n || l.getUTCMonth() !== r || l.getUTCDate() !== i) return K;
	if (t[9]) {
		let e = +t[10], n = +(t[11] || 0);
		if (e > 23 || n > 59) return K;
		let r = (e * 60 + n) * 6e4;
		l.setTime(l.getTime() - (t[9] === "-" ? -r : r));
	}
	return l;
}
var Vl = q("tag:yaml.org,2002:timestamp", {
	implicit: !0,
	implicitFirstChars: [..."0123456789"],
	resolve: Bl,
	identify: (e) => e instanceof Date,
	represent: (e) => e.toISOString()
}), Hl = Bc("tag:yaml.org,2002:seq", {
	create: () => [],
	addItem: (e, t) => {
		e.push(t);
	},
	identify: Array.isArray
});
function Ul(e) {
	if (typeof e != "object" || !e || Array.isArray(e)) return !1;
	let t = Object.getPrototypeOf(e);
	return t === null || t === Object.prototype;
}
function Wl(e, t) {
	let n = {};
	for (let r of t) e[r] !== void 0 && (n[r] = e[r]);
	return n;
}
var Gl = Bc("tag:yaml.org,2002:omap", {
	create: () => ({
		list: [],
		seen: /* @__PURE__ */ new Set()
	}),
	addItem: (e, t) => {
		let n;
		if (t instanceof Map) {
			if (t.size !== 1) return "cannot resolve an ordered map item";
			n = t.keys().next().value;
		} else if (Ul(t)) {
			let e = Object.keys(t);
			if (e.length !== 1) return "cannot resolve an ordered map item";
			n = e[0];
		} else return "cannot resolve an ordered map item";
		return e.seen.has(n) ? "duplicate key in ordered map" : (e.seen.add(n), e.list.push(t), "");
	},
	finalize: (e) => e.list,
	identify: () => !1
}), Kl = Bc("tag:yaml.org,2002:pairs", {
	create: () => [],
	addItem: (e, t) => {
		if (t instanceof Map) return t.size === 1 ? (e.push(t.entries().next().value), "") : "cannot resolve a pairs item";
		if (Object.prototype.toString.call(t) !== "[object Object]") return "cannot resolve a pairs item";
		let n = t, r = Object.keys(n);
		return r.length === 1 ? (e.push([r[0], n[r[0]]]), "") : "cannot resolve a pairs item";
	},
	identify: () => !1
}), ql = Vc("tag:yaml.org,2002:map", {
	create: () => ({}),
	identify: Ul,
	represent: (e) => {
		let t = /* @__PURE__ */ new Map();
		for (let n of Object.keys(e)) t.set(n, e[n]);
		return t;
	},
	addPair: (e, t, n) => {
		if (typeof t == "object" && t) return "object-based map does not support complex keys";
		let r = String(t);
		return r === "__proto__" ? Object.defineProperty(e, r, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : e[r] = n, "";
	},
	has: (e, t) => typeof t == "object" && t ? !1 : Object.prototype.hasOwnProperty.call(e, String(t)),
	keys: (e) => Object.keys(e),
	get: (e, t) => {
		let n = String(t);
		return Object.prototype.hasOwnProperty.call(e, n) ? e[n] : null;
	}
}), Jl = Vc("tag:yaml.org,2002:set", {
	create: () => /* @__PURE__ */ new Set(),
	identify: (e) => e instanceof Set,
	represent: (e) => {
		let t = /* @__PURE__ */ new Map();
		for (let n of e) t.set(n, null);
		return t;
	},
	addPair: (e, t, n) => n === null ? (e.add(t), "") : "cannot resolve a set item",
	has: (e, t) => e.has(t),
	keys: (e) => e.keys(),
	get: () => null
});
function Yl() {
	return {
		scalar: Object.create(null),
		sequence: Object.create(null),
		mapping: Object.create(null)
	};
}
function Xl() {
	return {
		scalar: [],
		sequence: [],
		mapping: []
	};
}
function Zl(e) {
	let t = [];
	for (let n of e) {
		let e = t.length;
		for (let r = 0; r < t.length; r++) {
			let i = t[r];
			if (i.nodeKind === n.nodeKind && i.tagName === n.tagName && i.matchByTagPrefix === n.matchByTagPrefix) {
				e = r;
				break;
			}
		}
		t[e] = n;
	}
	return t;
}
var Ql = class e {
	tags;
	implicitScalarTags;
	implicitScalarByFirstChar;
	implicitScalarAnyFirstChar;
	defaultScalarTag;
	defaultSequenceTag;
	defaultMappingTag;
	exact;
	prefix;
	constructor(e) {
		let t = Zl(e), n = [], r = Yl(), i = Xl();
		for (let e of t) {
			if (e.nodeKind === "scalar" && e.implicit) {
				if (e.matchByTagPrefix) throw Error("Implicit scalar tags cannot match by tag prefix");
				n.push(e);
			}
			switch (e.nodeKind) {
				case "scalar":
					e.matchByTagPrefix ? i.scalar.push(e) : r.scalar[e.tagName] = e;
					break;
				case "sequence":
					e.matchByTagPrefix ? i.sequence.push(e) : r.sequence[e.tagName] = e;
					break;
				case "mapping": e.matchByTagPrefix ? i.mapping.push(e) : r.mapping[e.tagName] = e;
			}
		}
		let a = n.filter((e) => e.implicitFirstChars === null), o = /* @__PURE__ */ new Set();
		for (let e of n) if (e.implicitFirstChars !== null) for (let t of e.implicitFirstChars) o.add(t);
		let s = /* @__PURE__ */ new Map();
		for (let e of o) s.set(e, n.filter((t) => t.implicitFirstChars === null || t.implicitFirstChars.indexOf(e) !== -1));
		let c = r.scalar["tag:yaml.org,2002:str"];
		if (!c) throw Error("schema does not define the default scalar tag (tag:yaml.org,2002:str)");
		this.tags = t, this.implicitScalarTags = n, this.implicitScalarByFirstChar = s, this.implicitScalarAnyFirstChar = a, this.defaultScalarTag = c, this.defaultSequenceTag = r.sequence["tag:yaml.org,2002:seq"], this.defaultMappingTag = r.mapping["tag:yaml.org,2002:map"], this.exact = r, this.prefix = i;
	}
	lookupScalarTag(e) {
		let t = this.exact.scalar[e];
		if (t) return t;
		for (let t of this.prefix.scalar) if (e.startsWith(t.tagName)) return t;
	}
	lookupSequenceTag(e) {
		let t = this.exact.sequence[e];
		if (t) return t;
		for (let t of this.prefix.sequence) if (e.startsWith(t.tagName)) return t;
	}
	lookupMappingTag(e) {
		let t = this.exact.mapping[e];
		if (t) return t;
		for (let t of this.prefix.mapping) if (e.startsWith(t.tagName)) return t;
	}
	resolveImplicitScalarTag(e) {
		let t = this.implicitScalarByFirstChar.get(e.charAt(0)) ?? this.implicitScalarAnyFirstChar;
		for (let n of t) {
			let t = n.resolve(e, !1, n.tagName);
			if (t !== K) return {
				value: t,
				tag: n
			};
		}
		let n = this.defaultScalarTag;
		return {
			value: n.resolve(e, !1, n.tagName),
			tag: n
		};
	}
	withTags(...t) {
		let n = [];
		for (let e of t) n = n.concat(e);
		return new e([...this.tags, ...n]);
	}
}, $l = new Ql([
	Hc,
	Hl,
	ql
]);
new Ql([
	...$l.tags,
	Gc,
	$c,
	fl,
	El
]);
var eu = new Ql([
	...$l.tags,
	Wc,
	Xc,
	sl,
	xl
]), tu = new Ql([
	...$l.tags,
	qc,
	nl,
	gl,
	jl,
	Vl,
	Ml,
	Il,
	Gl,
	Kl,
	Jl
]).withTags({
	...gl,
	resolve: (e, t, n) => {
		let r = gl.resolve(e, t, n);
		return r === K ? sl.resolve(e, t, n) : r;
	}
}, {
	...jl,
	resolve: (e, t, n) => {
		let r = jl.resolve(e, t, n);
		return r === K ? xl.resolve(e, t, n) : r;
	}
});
Vc("tag:yaml.org,2002:map", {
	create: () => /* @__PURE__ */ new Map(),
	addPair: (e, t, n) => (e.set(t, n), ""),
	has: (e, t) => e.has(t),
	keys: (e) => e.keys(),
	get: (e, t) => e.get(t),
	identify: (e) => e instanceof Map || Ul(e),
	represent: (e) => {
		if (e instanceof Map) return e;
		let t = /* @__PURE__ */ new Map(), n = e;
		for (let e of Object.keys(n)) t.set(e, n[e]);
		return t;
	}
});
function nu(e) {
	if (Array.isArray(e)) {
		let t = Array.prototype.slice.call(e);
		for (let e = 0; e < t.length; e++) {
			if (Array.isArray(t[e])) return null;
			typeof t[e] == "object" && Object.prototype.toString.call(t[e]) === "[object Object]" && (t[e] = "[object Object]");
		}
		return String(t);
	}
	return typeof e == "object" && Object.prototype.toString.call(e) === "[object Object]" ? "[object Object]" : String(e);
}
Vc("tag:yaml.org,2002:map", {
	create: () => ({}),
	identify: Ul,
	represent: (e) => {
		let t = /* @__PURE__ */ new Map();
		for (let n of Object.keys(e)) t.set(n, e[n]);
		return t;
	},
	addPair: (e, t, n) => {
		let r = nu(t);
		return r === null ? "nested arrays are not supported inside keys" : (r === "__proto__" ? Object.defineProperty(e, r, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : e[r] = n, "");
	},
	has: (e, t) => {
		let n = nu(t);
		return n !== null && Object.prototype.hasOwnProperty.call(e, n);
	},
	keys: (e) => Object.keys(e),
	get: (e, t) => {
		let n = String(t);
		return Object.prototype.hasOwnProperty.call(e, n) ? e[n] : null;
	}
});
var ru = {
	maxLength: 79,
	indent: 1,
	linesBefore: 3,
	linesAfter: 2
};
function iu(e, t, n, r, i) {
	let a = "", o = "", s = Math.floor(i / 2) - 1;
	return r - t > s && (a = " ... ", t = r - s + a.length), n - r > s && (o = " ...", n = r + s - o.length), {
		str: a + e.slice(t, n).replace(/\t/g, "→") + o,
		pos: r - t + a.length
	};
}
function au(e, t) {
	return " ".repeat(Math.max(t - e.length, 0)) + e;
}
function ou(e, t) {
	if (!e.buffer) return null;
	let n = {
		...ru,
		...t
	}, r = /\r?\n|\r|\0/g, i = [0], a = [], o, s = -1;
	for (; o = r.exec(e.buffer);) a.push(o.index), i.push(o.index + o[0].length), e.position <= o.index && s < 0 && (s = i.length - 2);
	s < 0 && (s = i.length - 1);
	let c = "", l = Math.min(e.line + n.linesAfter, a.length).toString().length, u = n.maxLength - (n.indent + l + 3);
	for (let t = 1; t <= n.linesBefore && !(s - t < 0); t++) {
		let r = iu(e.buffer, i[s - t], a[s - t], e.position - (i[s] - i[s - t]), u);
		c = `${" ".repeat(n.indent)}${au((e.line - t + 1).toString(), l)} | ${r.str}\n${c}`;
	}
	let d = iu(e.buffer, i[s], a[s], e.position, u);
	c += `${" ".repeat(n.indent)}${au((e.line + 1).toString(), l)} | ${d.str}\n`, c += `${"-".repeat(n.indent + l + 3 + d.pos)}^\n`;
	for (let t = 1; t <= n.linesAfter && !(s + t >= a.length); t++) {
		let r = iu(e.buffer, i[s + t], a[s + t], e.position - (i[s] - i[s + t]), u);
		c += `${" ".repeat(n.indent)}${au((e.line + t + 1).toString(), l)} | ${r.str}\n`;
	}
	return c.replace(/\n$/, "");
}
function su(e, t) {
	let n = "";
	return e.mark ? (e.mark.name && (n += `in "${e.mark.name}" `), n += `(${e.mark.line + 1}:${e.mark.column + 1})`, !t && e.mark.snippet && (n += `\n\n${e.mark.snippet}`), `${e.reason} ${n}`) : e.reason;
}
var cu = class e extends Error {
	reason;
	mark;
	constructor(e, t) {
		super(), this.name = "YAMLException", this.reason = e, this.mark = t, this.message = su(this, !1), Error.captureStackTrace && Error.captureStackTrace(this, this.constructor);
	}
	toString(e) {
		return `${this.name}: ${su(this, e)}`;
	}
	static throwAt(t, n, r, i = "") {
		let a = 0, o = 0;
		for (let e = 0; e < n; e++) {
			let n = t.charCodeAt(e);
			n === 10 ? (a++, o = e + 1) : n === 13 && (a++, t.charCodeAt(e + 1) === 10 && e++, o = e + 1);
		}
		let s = {
			name: i,
			buffer: t,
			position: n,
			line: a,
			column: n - o
		};
		throw s.snippet = ou(s), new e(r, s);
	}
}, lu = {
	DOCUMENT: 1,
	SEQUENCE: 2,
	MAPPING: 3,
	SCALAR: 4,
	ALIAS: 5,
	POP: 6
}, J = {
	PLAIN: 1,
	SINGLE_QUOTED: 2,
	DOUBLE_QUOTED: 3,
	LITERAL_BLOCK: 4,
	FOLDED_BLOCK: 5
}, Y = {
	BLOCK: 1,
	FLOW: 2
}, uu = {
	CLIP: 1,
	STRIP: 2,
	KEEP: 3
};
function du(e) {
	switch (e) {
		case 48: return "\0";
		case 97: return "\x07";
		case 98: return "\b";
		case 116: return "	";
		case 9: return "	";
		case 110: return "\n";
		case 118: return "\v";
		case 102: return "\f";
		case 114: return "\r";
		case 101: return "\x1B";
		case 32: return " ";
		case 34: return "\"";
		case 47: return "/";
		case 92: return "\\";
		case 78: return "";
		case 95: return "\xA0";
		case 76: return "\u2028";
		case 80: return "\u2029";
		default: return "";
	}
}
var fu = Array(256), pu = Array(256);
for (let e = 0; e < 256; e++) fu[e] = +!!du(e), pu[e] = du(e);
Object.assign(Object.create(null), {
	"!": "!",
	"!!": "tag:yaml.org,2002:"
});
function mu(e) {
	return encodeURI(e).replace(/!/g, "%21");
}
function hu(e) {
	let t = e;
	return t.charCodeAt(0) === 33 ? (t = t.slice(1), `!${mu(t)}`) : t.slice(0, 18) === "tag:yaml.org,2002:" ? `!!${mu(t.slice(18))}` : `!<${mu(t)}>`;
}
var gu = {
	filename: "",
	schema: eu,
	json: !1,
	maxTotalMergeKeys: 1e4,
	maxAliases: -1
}, _u = String.raw`(?:%[0-9A-Fa-f]{2}|[0-9A-Za-z\-#;/?:@&=+$,_.!~*'()\[\]])`, vu = String.raw`(?:%[0-9A-Fa-f]{2}|[0-9A-Za-z\-#;/?:@&=+$.~*'()_])`;
RegExp(`^(?:${_u})*$`), RegExp(`^(?:${vu})+$`), RegExp(`^(?:!(?:${_u})*|${vu}(?:${_u})*)$`), { ...gu };
var yu = Symbol("INVALID");
function bu(e) {
	let t = new Set([
		e.defaultScalarTag,
		e.defaultSequenceTag,
		e.defaultMappingTag
	].filter((e) => e !== void 0)), n = e.implicitScalarTags, r = e.tags.filter((e) => !(e.nodeKind === "scalar" && e.implicit) && !t.has(e)), i = e.tags.filter((e) => t.has(e));
	return [
		...n.map((e) => ({
			tag: e,
			implicitTag: !0
		})),
		...r.map((e) => ({
			tag: e,
			implicitTag: !1
		})),
		...i.map((e) => ({
			tag: e,
			implicitTag: !0
		}))
	];
}
function xu(e, t) {
	for (let n = 0, r = e.representTypes.length; n < r; n += 1) {
		let { tag: r, implicitTag: i } = e.representTypes[n];
		if (r.identify(t)) {
			let e;
			return e = r.matchByTagPrefix ? r.representTagName(t) : r.tagName, {
				tag: r,
				tagName: e,
				implicitTag: i
			};
		}
	}
	return null;
}
function Su(e, t) {
	if (!e.noRefs && typeof t == "object" && t) {
		let n = e.refs.get(t);
		if (n) return n.anchor === void 0 && (n.anchor = `ref_${e.refCounter++}`), {
			kind: "alias",
			anchor: n.anchor
		};
	}
	let n = xu(e, t);
	if (!n) {
		if (t === void 0 || e.skipInvalid) return yu;
		throw new cu(`unacceptable kind of an object to dump ${Object.prototype.toString.call(t)}`);
	}
	let { tag: r, tagName: i, implicitTag: a } = n, o = a ? i : hu(i);
	if (r.nodeKind === "scalar") return {
		kind: "scalar",
		tag: o,
		tagged: !a,
		style: J.PLAIN,
		value: r.represent(t)
	};
	if (r.nodeKind === "sequence") {
		let n = r.represent(t), i = {
			kind: "sequence",
			tag: o,
			tagged: !a,
			style: Y.BLOCK,
			items: []
		};
		e.noRefs || e.refs.set(t, i);
		for (let t = 0, r = n.length; t < r; t += 1) {
			let r = Su(e, n[t]);
			r === yu && n[t] === void 0 && (r = Su(e, null)), r !== yu && i.items.push(r);
		}
		return i;
	}
	let s = r.represent(t), c = {
		kind: "mapping",
		tag: o,
		tagged: !a,
		style: Y.BLOCK,
		items: []
	};
	e.noRefs || e.refs.set(t, c);
	for (let [t, n] of s) {
		let r = Su(e, t);
		if (r === yu) continue;
		let i = Su(e, n);
		i !== yu && c.items.push({
			key: r,
			value: i
		});
	}
	return c;
}
function Cu(e, t, n = {}) {
	let r = Su({
		representTypes: bu(t),
		noRefs: n.noRefs ?? !1,
		skipInvalid: n.skipInvalid ?? !1,
		refs: /* @__PURE__ */ new Map(),
		refCounter: 0
	}, e);
	return [{
		contents: r === yu ? null : r,
		directives: []
	}];
}
var wu = Symbol("visit:break"), Tu = Symbol("visit:skip");
function Eu(e, t, n) {
	let r = t(e, n);
	if (r === wu) return !0;
	if (r === Tu) return !1;
	let i = n.depth + 1;
	switch (e.kind) {
		case "sequence":
			for (let n of e.items) if (Eu(n, t, {
				depth: i,
				parent: e,
				isKey: !1
			})) return !0;
			break;
		case "mapping": for (let { key: n, value: r } of e.items) if (Eu(n, t, {
			depth: i,
			parent: e,
			isKey: !0
		}) || Eu(r, t, {
			depth: i,
			parent: e,
			isKey: !1
		})) return !0;
	}
	return !1;
}
function Du(e, t) {
	for (let n of e) if (n.contents && Eu(n.contents, t, {
		depth: 0,
		parent: null,
		isKey: !1
	})) return;
}
function Ou(e, t) {
	return !!(e & 1 << t);
}
var ku = {
	applyQuoteFlowKeysOption: ju,
	doubleQuoteForInvisibles: Mu,
	doubleQuoteWhitespaceOnly: Nu,
	applyForceQuotesOption: Pu,
	tryLongOrMultilineAsBlock: Fu,
	quoteInvalidPlain: Iu,
	fallbackToDoubleQuoted: Lu
};
function Au(e) {
	return e.presenterOptions.quoteStyle === "single" && Ou(e.allowedStylesMask, J.SINGLE_QUOTED) ? J.SINGLE_QUOTED : J.DOUBLE_QUOTED;
}
function ju(e) {
	e.presenterOptions.quoteFlowKeys && e.isKey && e.flowOnly && e.style === J.PLAIN && (e.style = J.DOUBLE_QUOTED);
}
function Mu(e) {
	e.style === J.PLAIN && /[\t\x7F-\xA0\u2028\u2029\uFEFF\uFFFE\uFFFF]/.test(e.node.value) && (e.style = J.DOUBLE_QUOTED);
}
function Nu(e) {
	e.style === J.PLAIN && /^\s+$/.test(e.node.value) && (e.style = J.DOUBLE_QUOTED);
}
function Pu(e) {
	e.presenterOptions.forceQuotes && (e.isKey || e.style !== J.PLAIN || e.node.tag === e.presenterOptions.schema.defaultScalarTag.tagName && (e.style = e.node.value.includes("\n") ? J.DOUBLE_QUOTED : Au(e)));
}
function Fu(e) {
	if (e.style !== J.PLAIN || e.isKey) return;
	let t = e.node.value, n = t.indexOf("\n") !== -1;
	if (!Ou(e.allowedStylesMask, J.LITERAL_BLOCK)) {
		n && (e.style = J.DOUBLE_QUOTED);
		return;
	}
	let r = e.presenterOptions.lineWidth;
	if (r === -1) {
		n && (e.style = J.LITERAL_BLOCK);
		return;
	}
	let i = Math.max(Math.min(r, 40), r - e.shiftOfContent), a = 0, o = !1;
	for (; a <= t.length;) {
		let e = t.length, n = t.indexOf("\n", a);
		n !== -1 && (e = n);
		let r = t.slice(a, e);
		if (r.length > i && r[0] !== " " && / [^ \t]/.test(r) && (o = !0), n === -1) break;
		a = n + 1;
	}
	o ? e.style = J.FOLDED_BLOCK : n && (e.style = J.LITERAL_BLOCK);
}
function Iu(e) {
	e.style === J.PLAIN && !Ou(e.allowedStylesMask, J.PLAIN) && (e.style = Au(e));
}
function Lu(e) {
	Ou(e.allowedStylesMask, e.style) || (e.style = J.DOUBLE_QUOTED);
}
function Ru(e, t) {
	return e | 1 << t;
}
var zu = "[\\x09\\x0A\\x0D\\x20-\\x7E\\x85\\xA0-\\uD7FF\\uE000-\\uFFFD\\u{10000}-\\u{10FFFF}]", Bu = "[\\n\\r]", Vu = "\\uFEFF", Hu = "[ \\t]", Uu = `(?:(?!(?:${Bu}|${Vu}))${zu})`, Wu = `(?:(?!${Hu})${Uu})`, Gu = "[\\x09\\x20-\\uD7FF\\uE000-\\uFFFF\\u{10000}-\\u{10FFFF}]", Ku = "[-?:,\\[\\]{}#&*!|>'\"%@`]", qu = "[,\\[\\]{}]", Ju = Wu, Yu = `(?:(?!${qu})${Wu})`, Xu = `(?:(?:(?!${Ku})${Wu})|[?:-](?=${Ju}))`, Zu = `(?:(?:(?!${Ku})${Wu})|[?:-](?=${Yu}))`, Qu = `(?:(?:(?![:#])${Ju})|:(?=${Ju}))#*`, $u = `(?:(?:(?![:#])${Yu})|:(?=${Yu}))#*`, ed = `(?:${Hu}*${Qu})*`, td = `(?:${Hu}*${$u})*`, nd = `${Xu}#*${ed}`, rd = `${Zu}#*${td}`, id = nd, ad = rd, od = `\\n+${Qu}${ed}`, sd = `\\n+${$u}${td}`, cd = `${nd}(?:${od})*`, ld = `${rd}(?:${sd})*`, ud = RegExp(`^(?:${cd})$`, "u"), dd = RegExp(`^(?:${ld})$`, "u"), fd = RegExp(`^(?:${id})$`, "u"), pd = RegExp(`^(?:${ad})$`, "u"), md = RegExp(`^(?:${Gu})*$`, "u"), hd = RegExp(`^(?:${Gu}|\\n)*$`, "u"), gd = RegExp(`^(?:${Uu}|\\n)*$`, "u"), _d = /^(?:---|\.\.\.)(?=$|[ \t\n\r])/, vd = /^(?:---|\.\.\.)(?=$|[ \t\n\r])/m;
function yd(e) {
	let t = e.node.value;
	if (t !== "") {
		if (!(e.isKey ? e.flowOnly ? pd : fd : e.flowOnly ? dd : ud).test(t) || e.shiftOfFirstLine === 0 && _d.test(t)) return !1;
		if (e.shiftOfContent === 0) {
			let e = t.indexOf("\n");
			if (e !== -1) {
				let n = t.slice(e + 1);
				if (vd.test(n)) return !1;
			}
		}
	}
	let n = e.presenterOptions.schema.resolveImplicitScalarTag(t).tag.tagName;
	return !(!e.node.tagged && n !== e.node.tag || !e.node.tagged && t === "=" && n === e.presenterOptions.schema.defaultScalarTag.tagName);
}
function bd(e) {
	let t = e.node.value;
	if (!(e.isKey ? md : hd).test(t) || /[ \t]\n|\n[ \t]/.test(t)) return !1;
	if (!e.isKey && e.shiftOfContent === 0) {
		let e = t.indexOf("\n");
		if (e !== -1 && vd.test(t.slice(e + 1))) return !1;
	}
	return !0;
}
function xd(e) {
	if (e.flowOnly || !gd.test(e.node.value)) return !1;
	let t = e.shiftOfContent - e.shiftOfParent;
	return !(t < 1 || t > 9 && /^\n* /.test(e.node.value) || e.shiftOfContent === 0 && vd.test(e.node.value));
}
function Sd(e) {
	let t = Ru(0, J.DOUBLE_QUOTED);
	yd(e) && (t = Ru(t, J.PLAIN)), bd(e) && (t = Ru(t, J.SINGLE_QUOTED)), xd(e) && (t = Ru(Ru(t, J.LITERAL_BLOCK), J.FOLDED_BLOCK)), e.allowedStylesMask = t;
}
function Cd(e) {
	switch (e.style) {
		case J.PLAIN: return wd(e);
		case J.SINGLE_QUOTED: return Td(e);
		case J.LITERAL_BLOCK: return Ed(e);
		case J.FOLDED_BLOCK: return Dd(e);
		case J.DOUBLE_QUOTED: return Od(e);
	}
}
function wd(e) {
	return kd(e.node.value, e.shiftOfContent);
}
function Td(e) {
	return `'${kd(e.node.value, e.shiftOfContent).replace(/'/g, "''")}'`;
}
function Ed(e) {
	let t = e.node.value;
	return "|" + Md(t, e.shiftOfParent, e.shiftOfContent) + Nd(Ad(t, e.shiftOfContent));
}
function Dd(e) {
	let t = e.node.value, n = e.presenterOptions.lineWidth, r = Infinity;
	return n !== -1 && (r = Math.max(Math.min(n, 40), n - e.shiftOfContent)), ">" + Md(t, e.shiftOfParent, e.shiftOfContent) + Nd(Ad(Id(t, r), e.shiftOfContent));
}
function Od(e) {
	return `"${zd(e.node.value)}"`;
}
function kd(e, t) {
	let n = e.indexOf("\n");
	if (n === -1) return e;
	let r = " ".repeat(t), i = e.slice(0, n), a = /(\n+)([^\n]*)/g;
	a.lastIndex = n;
	let o;
	for (; o = a.exec(e);) {
		let e = o[1].length, t = o[2];
		i += "\n".repeat(e + 1) + r + t;
	}
	return i;
}
function Ad(e, t) {
	let n = " ".repeat(t), r = 0, i = "", a = e.length;
	for (; r < a;) {
		let t, o = e.indexOf("\n", r);
		o === -1 ? (t = e.slice(r), r = a) : (t = e.slice(r, o + 1), r = o + 1), t.length && t !== "\n" && (i += n), i += t;
	}
	return i;
}
function jd(e) {
	return /^\n* /.test(e);
}
function Md(e, t, n) {
	let r = jd(e) ? String(n - t) : "", i = e[e.length - 1] === "\n";
	return `${r}${i && (e[e.length - 2] === "\n" || e === "\n") ? "+" : i ? "" : "-"}\n`;
}
function Nd(e) {
	return e[e.length - 1] === "\n" ? e.slice(0, -1) : e;
}
function Pd(e) {
	return e === " " || e === "	";
}
function Fd(e, t) {
	if (e === "" || Pd(e[0])) return e;
	let n = / [^ \t]/g, r, i = 0, a, o = 0, s = 0, c = "";
	for (; r = n.exec(e);) s = r.index, s - i > t && (a = o > i ? o : s, c += `\n${e.slice(i, a)}`, i = a + 1), o = s;
	return c += "\n", e.length - i > t && o > i ? c += `${e.slice(i, o)}\n${e.slice(o + 1)}` : c += e.slice(i), c.slice(1);
}
function Id(e, t) {
	let n = /(\n+)([^\n]*)/g, r = e.indexOf("\n");
	r === -1 && (r = e.length), n.lastIndex = r;
	let i = Fd(e.slice(0, r), t), a = e[0] === "\n" || Pd(e[0]), o, s;
	for (; s = n.exec(e);) {
		let e = s[1], n = s[2];
		o = n !== "" && Pd(n[0]), i += e + (!a && !o && n !== "" ? "\n" : "") + Fd(n, t), a = o;
	}
	return i;
}
var Ld = /["\\\x00-\x1F\x7F-\xA0\u2028\u2029\uD800-\uDFFF\uFEFF\uFFFE\uFFFF]/gu;
function Rd(e) {
	switch (e) {
		case "\0": return "\\0";
		case "\x07": return "\\a";
		case "\b": return "\\b";
		case "	": return "\\t";
		case "\n": return "\\n";
		case "\v": return "\\v";
		case "\f": return "\\f";
		case "\r": return "\\r";
		case "\x1B": return "\\e";
		case "\"": return "\\\"";
		case "\\": return "\\\\";
		case "": return "\\N";
		case "\xA0": return "\\_";
		case "\u2028": return "\\L";
		case "\u2029": return "\\P";
	}
	let t = e.charCodeAt(0), n = t.toString(16).toUpperCase();
	return t <= 255 ? `\\x${"0".repeat(2 - n.length)}${n}` : `\\u${"0".repeat(4 - n.length)}${n}`;
}
function zd(e) {
	return e.replace(Ld, Rd);
}
var Bd = 10, Vd = {
	indent: 2,
	seqNoIndent: !1,
	seqInlineFirst: !0,
	lineWidth: 80,
	flowBracketPadding: !1,
	flowSkipCommaSpace: !1,
	flowSkipColonSpace: !1,
	quoteFlowKeys: !1,
	quoteStyle: "single",
	forceQuotes: !1,
	scalarStyleRules: Object.keys(ku).map((e) => Reflect.get(ku, e)),
	tagBeforeAnchor: !1
};
function Hd(e) {
	return e.tagged ? e.tag : hu(e.tag);
}
function Ud(e) {
	let t = {
		...Vd,
		...e
	};
	return t.flowSkipColonSpace && (t.quoteFlowKeys = !0), {
		...t,
		defaultScalarTagName: t.schema.defaultScalarTag.tagName,
		openEnded: !1
	};
}
function Wd(e, t) {
	return `\n${" ".repeat(e.indent * t)}`;
}
function Gd(e, t, n, r, i, a) {
	return {
		node: t,
		parent: n,
		level: r,
		isKey: i,
		flowOnly: a,
		shiftOfParent: r === 0 ? -1 : e.indent * (r - 1),
		shiftOfContent: e.indent * Math.max(1, r),
		shiftOfFirstLine: r === 0 ? 0 : e.indent * r,
		presenterOptions: e,
		allowedStylesMask: 0,
		style: t.style
	};
}
function Kd(e, t, n) {
	let r = "";
	for (let i = 0, a = n.items.length; i < a; i += 1) {
		let a = X(e, t, n.items[i], n, {}).text;
		i > 0 && (r += `,${e.flowSkipCommaSpace ? "" : " "}`), r += a;
	}
	let i = e.flowBracketPadding && n.items.length > 0 ? " " : "";
	return `[${i}${r}${i}]`;
}
function qd(e, t, n, r) {
	let i = "";
	for (let a = 0, o = n.items.length; a < o; a += 1) {
		let o = X(e, t + 1, n.items[a], n, {
			block: !0,
			compact: e.seqInlineFirst,
			isblockseq: !0
		}).text;
		(!r || i !== "") && (i += Wd(e, t)), o === "" || Bd === o.charCodeAt(0) ? i += "-" : i += "- ", i += o;
	}
	return i;
}
function Jd(e, t, n) {
	let r = "";
	for (let { key: i, value: a } of n.items) {
		let o = "";
		r !== "" && (o += `,${e.flowSkipCommaSpace ? "" : " "}`);
		let s = X(e, t, i, n, { iskey: !0 }), c = s.text, l = X(e, t, a, n, {}).text, u = e.flowSkipColonSpace || l === "" ? "" : " ", d = i.kind === "scalar" && s.noBody && (i.tagged || i.anchor !== void 0), f = i.kind === "alias" || d ? " " : "";
		o += `${c}${f}:${u}${l}`, r += o;
	}
	let i = e.flowBracketPadding && r !== "" ? " " : "";
	return `{${i}${r}${i}}`;
}
function Yd(e, t, n, r) {
	let i = "";
	for (let a = 0, o = n.items.length; a < o; a += 1) {
		let o = "";
		(!r || i !== "") && (o += Wd(e, t));
		let { key: s, value: c } = n.items[a], l = (s.kind === "mapping" || s.kind === "sequence") && s.style === Y.BLOCK && s.items.length !== 0 || s.kind === "scalar" && (s.style === J.LITERAL_BLOCK || s.style === J.FOLDED_BLOCK), u = l ? X(e, t + 1, s, n, {
			block: !0,
			compact: !0,
			isblockseq: !Xd(e, s, t + 1)
		}) : X(e, t + 1, s, n, {
			block: !0,
			compact: !0,
			iskey: !0
		}), d = u.text, f = s.kind === "scalar" && s.value.indexOf("\n") !== -1, ee = d.length > 1024 && /^[\s\S]{1025}/u.test(d), p = l || f || ee;
		p && (d && Bd === d.charCodeAt(0) ? o += "?" : o += "? "), o += d, p && (o += Wd(e, t));
		let te = X(e, t + 1, c, n, {
			block: !0,
			compact: p,
			isblockseq: p && !Xd(e, c, t + 1)
		}).text, ne = s.kind === "scalar" && u.noBody && (s.tagged || s.anchor !== void 0), m = !p && (s.kind === "alias" || ne) ? " " : "";
		te === "" || Bd === te.charCodeAt(0) ? o += `${m}:` : o += `${m}: `, o += te, i += o;
	}
	return i;
}
function Xd(e, t, n) {
	return t.kind === "alias" || t.tagged || t.anchor !== void 0 || e.indent < 2 && n > 0;
}
function X(e, t, n, r, i) {
	if (n.kind === "alias") return e.openEnded = !1, {
		text: `*${n.anchor}`,
		noBody: !1
	};
	let { block: a = !1, iskey: o = !1, isblockseq: s = !1 } = i, c = i.compact ?? !1, l = n.anchor !== void 0;
	Xd(e, n, t) && (c = !1);
	let u, d = n.tagged, f = a && (n.kind === "mapping" || n.kind === "sequence") && n.style === Y.BLOCK && n.items.length !== 0;
	if (n.kind === "mapping") u = f ? Yd(e, t, n, c) : Jd(e, t, n);
	else if (n.kind === "sequence") u = f ? e.seqNoIndent && !s && t > 0 ? qd(e, t - 1, n, c) : qd(e, t, n, c) : Kd(e, t, n);
	else {
		let i = Gd(e, n, r, t, o, !a);
		Sd(i);
		for (let t of e.scalarStyleRules) t(i);
		u = Cd(i), e.openEnded = (i.style === J.LITERAL_BLOCK || i.style === J.FOLDED_BLOCK) && (n.value === "\n" || n.value.endsWith("\n\n")), d = n.tagged || u === "" && i.flowOnly && r?.kind === "sequence" && !l || i.style !== J.PLAIN && n.tag !== e.defaultScalarTagName;
	}
	(n.kind === "mapping" || n.kind === "sequence") && !f && (e.openEnded = !1), f && c && t > 0 && e.indent > 2 && (u = `${" ".repeat(e.indent - 2)}${u}`);
	let ee = u === "", p = u;
	if (d || l) {
		let t = [], r = d ? Hd(n) : null, i = l ? `&${n.anchor}` : null;
		e.tagBeforeAnchor ? (r !== null && t.push(r), i !== null && t.push(i)) : (i !== null && t.push(i), r !== null && t.push(r));
		let a = u === "" || u.charCodeAt(0) === Bd ? "" : " ";
		p = `${t.join(" ")}${a}${u}`;
	}
	return {
		text: p,
		noBody: ee
	};
}
function Zd(e) {
	return (e.kind === "sequence" || e.kind === "mapping") && e.style === Y.BLOCK && e.items.length !== 0 && !e.tagged && e.anchor === void 0;
}
function Qd(e) {
	let t = "";
	for (let n of e.directives) {
		if (n.kind === "yaml") {
			t += `%YAML ${n.version}\n`;
			continue;
		}
		let { handle: e, prefix: r } = n;
		t += `%TAG ${e} ${r}\n`;
	}
	return t;
}
function $d(e, t) {
	let n = Ud(t), r = "", i = !1;
	for (let t = 0; t < e.length; t += 1) {
		let a = e[t];
		n.openEnded = !1;
		let o = Qd(a), s = o !== "", c = a.explicitStart || s || t > 0 && !i;
		if (r += o, a.contents === null) c && (r += "---\n");
		else if (c) {
			let e = X(n, 0, a.contents, null, {
				block: !0,
				compact: !0
			}).text, t = e === "" ? "" : s || Zd(a.contents) ? "\n" : " ";
			r += `---${t}${e}\n`;
		} else r += X(n, 0, a.contents, null, {
			block: !0,
			compact: !0
		}).text + "\n";
		i = a.explicitEnd || n.openEnded, i && (r += "...\n");
	}
	return r;
}
var ef = {
	...Vd,
	schema: tu,
	skipInvalid: !1,
	noRefs: !1,
	flowLevel: -1,
	sortKeys: !1,
	transform: () => {}
};
function tf(e, t) {
	let n = String(e), r = String(t);
	return n < r ? -1 : +(n > r);
}
function nf(e, t = {}) {
	let n = {
		...ef,
		...t
	}, r = Cu(e, n.schema, {
		noRefs: n.noRefs,
		skipInvalid: n.skipInvalid
	});
	if (n.flowLevel >= 0 && Du(r, (e, t) => {
		if (!(t.depth < n.flowLevel)) return (e.kind === "sequence" || e.kind === "mapping") && (e.style = Y.FLOW), Tu;
	}), n.sortKeys) {
		let e = n.sortKeys === !0 ? tf : n.sortKeys;
		Du(r, (t) => {
			t.kind === "mapping" && t.items.sort((t, n) => e(t.key.kind === "scalar" ? t.key.value : "", n.key.kind === "scalar" ? n.key.value : ""));
		});
	}
	return n.transform(r), $d(r, {
		...Wl(n, Object.keys(Vd)),
		schema: n.schema
	});
}
lu.DOCUMENT, lu.SEQUENCE, lu.MAPPING, lu.SCALAR, lu.ALIAS, lu.POP, J.PLAIN, J.SINGLE_QUOTED, J.DOUBLE_QUOTED, J.LITERAL_BLOCK, J.FOLDED_BLOCK, Y.BLOCK, Y.FLOW, uu.CLIP, uu.STRIP, uu.KEEP, O.Admin.AdminMessage_ConfigType.DEVICE_CONFIG, O.Admin.AdminMessage_ConfigType.POSITION_CONFIG, O.Admin.AdminMessage_ConfigType.POWER_CONFIG, O.Admin.AdminMessage_ConfigType.NETWORK_CONFIG, O.Admin.AdminMessage_ConfigType.DISPLAY_CONFIG, O.Admin.AdminMessage_ConfigType.LORA_CONFIG, O.Admin.AdminMessage_ConfigType.BLUETOOTH_CONFIG, O.Admin.AdminMessage_ConfigType.SECURITY_CONFIG, O.Admin.AdminMessage_ConfigType.DEVICEUI_CONFIG, O.Admin.AdminMessage_ModuleConfigType.MQTT_CONFIG, O.Admin.AdminMessage_ModuleConfigType.SERIAL_CONFIG, O.Admin.AdminMessage_ModuleConfigType.EXTNOTIF_CONFIG, O.Admin.AdminMessage_ModuleConfigType.STOREFORWARD_CONFIG, O.Admin.AdminMessage_ModuleConfigType.RANGETEST_CONFIG, O.Admin.AdminMessage_ModuleConfigType.TELEMETRY_CONFIG, O.Admin.AdminMessage_ModuleConfigType.CANNEDMSG_CONFIG, O.Admin.AdminMessage_ModuleConfigType.AUDIO_CONFIG, O.Admin.AdminMessage_ModuleConfigType.REMOTEHARDWARE_CONFIG, O.Admin.AdminMessage_ModuleConfigType.NEIGHBORINFO_CONFIG, O.Admin.AdminMessage_ModuleConfigType.AMBIENTLIGHTING_CONFIG, O.Admin.AdminMessage_ModuleConfigType.DETECTIONSENSOR_CONFIG, O.Admin.AdminMessage_ModuleConfigType.PAXCOUNTER_CONFIG, O.Admin.AdminMessage_ModuleConfigType.STATUSMESSAGE_CONFIG, O.Admin.AdminMessage_ModuleConfigType.TRAFFICMANAGEMENT_CONFIG, O.Admin.AdminMessage_ModuleConfigType.TAK_CONFIG;
var Z = {
	pasoActual: 1,
	modo: "asistente",
	rolSeleccionado: "CLIENT_MUTE",
	potenciaTx: 27,
	provincia: "",
	nodoConectado: !1,
	dispositivo: null,
	transporte: null,
	myNodeNum: null,
	myNodeInfo: null,
	sessionPasskey: null,
	configSections: {},
	moduleConfigSections: {},
	channelMap: /* @__PURE__ */ new Map(),
	liveConfig: null,
	desiredConfig: null,
	qrInstance: null,
	pendingAdminResponses: []
};
function Q(e) {
	let t = `[${(/* @__PURE__ */ new Date()).toLocaleTimeString()}] ${e}\n`, n = document.getElementById("logTextarea");
	n && (n.value = t + n.value);
}
function rf() {
	let e = document.getElementById("inputLongName")?.value.trim() || "MiNodo-Andalucia", t = (document.getElementById("inputShortName")?.value.trim() || "AND1").slice(0, 4), n = Z.rolSeleccionado === "CLIENT_MUTE", r = n ? 4 : 3, i = n ? 21600 : 259200, a = Number(document.querySelector("input[name=\"txPowerSelect\"]:checked")?.value || 27), o = Number(document.getElementById("telemetriaSelect")?.value || 0), s = !!document.getElementById("chkMqtt")?.checked, c = document.getElementById("provinciaSelect")?.value || "", l = [{
		index: 0,
		role: "PRIMARY",
		settings: {
			name: "SFNarrow",
			psk: "AQ==",
			uplinkEnabled: !0,
			downlinkEnabled: !n,
			moduleSettings: { positionPrecision: 15 }
		}
	}], u = 1;
	c && l.push({
		index: u++,
		role: "SECONDARY",
		settings: {
			name: c,
			psk: "AQ==",
			uplinkEnabled: !0,
			downlinkEnabled: !0,
			moduleSettings: { positionPrecision: 15 }
		}
	});
	for (let e of [
		{
			id: "chkIberia",
			name: "Iberia"
		},
		{
			id: "chkAndalucia",
			name: "Andalucia"
		},
		{
			id: "chkTest",
			name: "Test"
		},
		{
			id: "chkBots",
			name: "Bots"
		},
		{
			id: "chkSos",
			name: "sos"
		}
	]) document.getElementById(e.id)?.checked && u < 8 && l.push({
		index: u++,
		role: "SECONDARY",
		settings: {
			name: e.name,
			psk: "AQ==",
			uplinkEnabled: !0,
			downlinkEnabled: !0
		}
	});
	for (; u < 8;) l.push({
		index: u++,
		role: "SECONDARY",
		settings: {}
	});
	let d = { telemetry: { deviceUpdateInterval: o } };
	s && (d.mqtt = {
		enabled: !0,
		address: "mqtt.desdechipiona.es",
		username: "meshdev",
		password: "large4cats",
		root: "msh",
		encryptionEnabled: !0,
		proxyToClientEnabled: !0,
		mapReportingEnabled: !1
	});
	let f = {
		owner: e,
		owner_short: t,
		is_unmessagable: !1,
		config: {
			device: {
				role: Z.rolSeleccionado,
				nodeInfoBroadcastSecs: 259200,
				rebroadcastMode: n ? "CORE_PORTNUMS_ONLY" : "ALL"
			},
			lora: {
				region: "EU_868",
				usePreset: !1,
				bandwidth: 62,
				spreadFactor: 7,
				codingRate: 5,
				channelNum: 4,
				hopLimit: r,
				txPower: a,
				txEnabled: !0,
				sx126xRxBoostedGain: !0
			},
			position: {
				positionBroadcastSmartEnabled: !1,
				positionBroadcastSecs: i,
				positionFlags: 0
			},
			security: { serialEnabled: !0 }
		},
		module_config: d,
		channels: l
	};
	Z.desiredConfig = f;
	let ee = nf(f, {
		lineWidth: 120,
		noRefs: !0,
		sortKeys: !1
	}), p = document.getElementById("desiredYamlTextarea");
	return p && (p.value = ee), {
		configDoc: f,
		yamlText: ee
	};
}
function $(e) {
	let t = [];
	for (; e > 127;) t.push(e & 127 | 128);
	return t.push(e & 127), t;
}
function af(e, t = [1], n = !0, r = !0) {
	let i = new TextEncoder().encode(e), a = [
		18,
		t.length,
		...t,
		26,
		i.length,
		...i,
		40,
		+!!n,
		48,
		+!!r
	];
	return [
		10,
		...$(a.length),
		...a
	];
}
function of(e = 62, t = 7, n = 5, r = 4, i = 4, a = 27) {
	let o = [
		24,
		...$(e),
		32,
		...$(t),
		40,
		...$(n),
		56,
		3,
		64,
		...$(i),
		80,
		...$(a),
		88,
		...$(r)
	];
	return [
		18,
		...$(o.length),
		...o
	];
}
function sf(e) {
	let t = [];
	if (Array.isArray(e.channels)) {
		for (let n of e.channels) if (n.settings && n.settings.name) {
			let r = n.role === "PRIMARY" ? e.config.device.role !== "CLIENT_MUTE" : n.settings.downlinkEnabled ?? !0, i = n.settings.uplinkEnabled ?? !0;
			t.push(...af(n.settings.name, [1], i, r));
		}
	}
	let n = e.config.lora;
	t.push(...of(n.bandwidth, n.spreadFactor, n.codingRate, n.channelNum, n.hopLimit, n.txPower));
	let r = new Uint8Array(t), i = "";
	for (let e = 0; e < r.byteLength; e++) i += String.fromCharCode(r[e]);
	return `https://meshtastic.org/e/#${btoa(i).replaceAll("+", "-").replaceAll("/", "_").replaceAll("=", "")}`;
}
function cf() {
	let { configDoc: e } = rf(), t = e.owner, n = e.owner_short, r = document.getElementById("previewAvatar"), i = document.getElementById("previewLongName"), a = document.getElementById("previewShortName");
	r && (r.textContent = t.charAt(0).toUpperCase()), i && (i.textContent = t), a && (a.textContent = n);
	try {
		let t = sf(e), n = document.getElementById("qrShareUrl");
		n && (n.value = t);
		let r = document.getElementById("qrCanvasContainer");
		r && typeof window.QRCode == "function" && (r.innerHTML = "", new window.QRCode(r, {
			text: t,
			width: 220,
			height: 220,
			colorDark: "#2C2D3C",
			colorLight: "#FFFFFF",
			correctLevel: window.QRCode.CorrectLevel.M
		}));
	} catch (e) {
		console.error("Error al generar URL o código QR:", e);
	}
}
function lf(e) {
	Z.pasoActual = e;
	for (let t = 1; t <= 4; t++) {
		let n = document.getElementById(`stepIndicator${t}`), r = document.getElementById(`stepPanel${t}`);
		n && (n.classList.toggle("active", t === e), n.classList.toggle("done", t < e)), r && (r.classList.toggle("active", t === e), r.style.display = t === e ? "block" : "none");
	}
	e === 4 && cf(), window.scrollTo({
		top: 0,
		behavior: "smooth"
	});
}
function uf(e) {
	Z.rolSeleccionado = e;
	let t = document.getElementById("cardRoleMute"), n = document.getElementById("cardRoleClient");
	t && t.classList.toggle("selected", e === "CLIENT_MUTE"), n && n.classList.toggle("selected", e === "CLIENT"), cf();
}
function df() {
	let { yamlText: e } = rf(), t = new Blob([e], { type: "text/yaml;charset=utf-8" }), n = URL.createObjectURL(t), r = document.createElement("a");
	r.href = n, r.download = "andalucia-mesh-sfnarrow.yaml", document.body.appendChild(r), r.click(), r.remove(), URL.revokeObjectURL(n), Q("Archivo andalucia-mesh-sfnarrow.yaml descargado con éxito.");
}
function ff() {
	let e = document.getElementById("qrShareUrl");
	e && e.value && navigator.clipboard.writeText(e.value).then(() => {
		alert("Enlace oficial de Meshtastic copiado al portapapeles."), Q("Enlace de canales copiado al portapapeles.");
	});
}
function pf() {
	let { configDoc: e } = rf(), t = e.config.lora, n = e.config.device, r = e.config.position, i = [
		"# Configuración oficial Andalucía Mesh (SFNarrow)",
		`meshtastic --set-owner "${e.owner}" --set-owner-short "${e.owner_short}"`,
		`meshtastic --set lora.region ${t.region} --set lora.use_preset false`,
		`meshtastic --set lora.bandwidth ${t.bandwidth} --set lora.spread_factor ${t.spreadFactor} --set lora.coding_rate ${t.codingRate}`,
		`meshtastic --set lora.channel_num ${t.channelNum} --set lora.hop_limit ${t.hopLimit} --set lora.tx_power ${t.txPower}`,
		`meshtastic --set device.role ${n.role} --set device.node_info_broadcast_secs ${n.nodeInfoBroadcastSecs}`,
		`meshtastic --set position.position_broadcast_smart_enabled false --set position.position_flags 0 --set position.position_broadcast_secs ${r.positionBroadcastSecs}`,
		"meshtastic --ch-set name \"SFNarrow\" --ch-set psk \"AQ==\" --ch-index 0"
	];
	if (e.channels && e.channels.length > 1) for (let t = 1; t < e.channels.length; t++) {
		let n = e.channels[t];
		n && n.settings && n.settings.name && i.push(`meshtastic --ch-set name "${n.settings.name}" --ch-set psk "${n.settings.psk || "AQ=="}" --ch-index ${t}`);
	}
	if (e.module_config?.mqtt?.enabled) {
		let t = e.module_config.mqtt;
		i.push(`meshtastic --set mqtt.enabled true --set mqtt.address "${t.address}" --set mqtt.username "${t.username}" --set mqtt.password "${t.password}" --set mqtt.encryption_enabled true`);
	}
	let a = i.join("\n");
	navigator.clipboard.writeText(a).then(() => {
		alert("Comandos CLI de Meshtastic copiados al portapapeles."), Q("Comandos CLI copiados al portapapeles.");
	});
}
async function mf() {
	let e = document.getElementById("transportSelect")?.value || "serial", t = document.getElementById("btnConnectDirect"), n = document.getElementById("btnDisconnectDirect"), r = document.getElementById("statusPill");
	try {
		t && (t.disabled = !0), Q(`Iniciando conexión directa por ${e}...`);
		let i;
		e === "serial" ? i = await Mc.create(115200) : e === "bluetooth" ? i = await Pc.create() : e === "http" && (i = new zc(document.getElementById("httpIpInput")?.value.trim() || "meshtastic.local", !1)), Z.transporte = i, Z.dispositivo = new jc(i), Z.nodoConectado = !0, n && (n.disabled = !1), r && (r.textContent = "⚡ Conectado", r.style.background = "var(--color-correcto-fondo)", r.style.color = "var(--color-correcto-texto)"), Q("Nodo conectado correctamente. Leyendo parámetros de radio..."), alert("¡Nodo conectado! Puedes aplicar la configuración ahora o inspeccionar en el modo Workbench.");
	} catch (e) {
		Q(`Error de conexión: ${e.message}`), alert(`No se pudo conectar al dispositivo: ${e.message}`), t && (t.disabled = !1);
	}
}
async function hf() {
	if (Z.dispositivo || Z.transporte) {
		try {
			Z.dispositivo ? await Z.dispositivo.disconnect() : Z.transporte && await Z.transporte.disconnect();
		} catch (e) {
			Q(`Error al desconectar: ${e.message}`);
		}
		Z.transporte = null, Z.dispositivo = null, Z.nodoConectado = !1;
		let e = document.getElementById("btnConnectDirect"), t = document.getElementById("btnDisconnectDirect"), n = document.getElementById("statusPill");
		e && (e.disabled = !1), t && (t.disabled = !0), n && (n.textContent = "🔌 Desconectado", n.style.background = "", n.style.color = ""), Q("Dispositivo desconectado.");
	}
}
async function gf() {
	if (!Z.dispositivo || !Z.nodoConectado) {
		alert("Debes conectar tu nodo primero para leer su configuración."), Q("Intento de lectura sin dispositivo conectado.");
		return;
	}
	Q("Leyendo configuración actual del dispositivo...");
	let e = document.getElementById("liveYamlTextarea");
	e && (e.value = `# Configuración leída del nodo Meshtastic\n# Estado: Conectado\n# ID: ${Z.myNodeNum ?? "Local"}`);
}
function _f() {
	let e = document.getElementById("liveYamlTextarea")?.value, t = document.getElementById("desiredYamlTextarea");
	e && t && (t.value = e, Q("Configuración leída copiada a panel deseado."));
}
async function vf() {
	!Z.dispositivo || !Z.nodoConectado ? alert("Conecta tu nodo por cable USB o Bluetooth para volcar los cambios.") : (Q("Aplicando configuración deseada al nodo..."), alert("Escribiendo configuración en el nodo. Por favor espera..."));
}
function yf() {
	document.getElementById("desiredYamlTextarea")?.value && (Z.desiredConfig = null);
}
function bf(e) {
	Z.modo = e;
	let t = document.getElementById("tabAssistantMode"), n = document.getElementById("tabWorkbenchMode"), r = document.getElementById("viewAssistant"), i = document.getElementById("viewWorkbench"), a = e === "asistente";
	t && t.classList.toggle("active", a), n && n.classList.toggle("active", !a), r && (r.style.display = a ? "block" : "none"), i && (i.style.display = a ? "none" : "block"), a || rf();
}
function xf() {
	let e = document.documentElement, t = (e.getAttribute("data-theme") || e.getAttribute("data-tema") || "dark") === "light" ? "dark" : "light";
	e.setAttribute("data-theme", t), e.setAttribute("data-tema", t);
	try {
		localStorage.setItem("snm_theme", t), localStorage.setItem("snm_tema", t);
	} catch {}
	let n = document.getElementById("themeIcon");
	n && (n.textContent = t === "light" ? "🌙" : "☀️");
}
window.irAlPaso = lf, window.seleccionarRol = uf, window.actualizarConfiguracion = cf, window.descargarYamlDeseado = df, window.copiarEnlaceQR = ff, window.copiarComandosCli = pf, window.conectarDispositivo = mf, window.desconectarDispositivo = hf, window.descargarConfiguracionNodo = gf, window.copiarLiveADeseado = _f, window.aplicarDeseadoANodo = vf, window.alEditarYamlDeseado = yf, window.setModo = bf, window.toggleTema = xf, window.limpiarLog = () => {
	let e = document.getElementById("logTextarea");
	e && (e.value = "");
}, document.addEventListener("click", (e) => {
	let t = e.target;
	if (!t) return;
	let n = t.closest("#tabAssistantMode, #tabWorkbenchMode");
	n && (n.id === "tabAssistantMode" && bf("asistente"), n.id === "tabWorkbenchMode" && bf("workbench"));
});
function Sf() {
	let e = document.documentElement.getAttribute("data-theme") || localStorage.getItem("snm_theme") || localStorage.getItem("snm_tema") || "dark";
	document.documentElement.setAttribute("data-theme", e), document.documentElement.setAttribute("data-tema", e);
	let t = document.getElementById("themeIcon");
	t && (t.textContent = e === "light" ? "🌙" : "☀️"), new MutationObserver((e) => {
		for (let n of e) if (n.type === "attributes" && (n.attributeName === "data-theme" || n.attributeName === "data-tema")) {
			let e = document.documentElement.getAttribute("data-theme") || document.documentElement.getAttribute("data-tema") || "dark";
			t && (t.textContent = e === "light" ? "🌙" : "☀️");
		}
	}).observe(document.documentElement, {
		attributes: !0,
		attributeFilter: ["data-theme", "data-tema"]
	}), document.getElementById("themeToggleBtn")?.addEventListener("click", xf), document.getElementById("tabAssistantMode")?.addEventListener("click", () => bf("asistente")), document.getElementById("tabWorkbenchMode")?.addEventListener("click", () => bf("workbench")), document.getElementById("transportSelect")?.addEventListener("change", (e) => {
		let t = e.target.value === "http", n = document.getElementById("httpIpGroup");
		n && (n.style.display = t ? "flex" : "none");
	}), cf(), Q("Configurador de Andalucía Mesh iniciado con preset SFNarrow.");
}
document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", Sf) : Sf();
//#endregion
export { cf as actualizarConfiguracion, yf as alEditarYamlDeseado, vf as aplicarDeseadoANodo, mf as conectarDispositivo, rf as construirYamlDeseado, pf as copiarComandosCli, ff as copiarEnlaceQR, _f as copiarLiveADeseado, gf as descargarConfiguracionNodo, df as descargarYamlDeseado, hf as desconectarDispositivo, lf as irAlPaso, uf as seleccionarRol, bf as setModo, xf as toggleTema };
