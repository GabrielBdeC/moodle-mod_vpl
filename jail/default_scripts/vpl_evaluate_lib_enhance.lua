local dirEnhance = "."

local lib_lua = require "./vpl_evaluate_lib_utils"

local enhance = {}

local function jsonFileGet(filename)
  return lib_lua.parse(lib_lua.readall(filename))
end

local function getDefaultIdAndTokenPos(info, file)
  local pos = {}
  for id, data in pairs(enhance.default) do
    if (string.find(data, "&$var" , 1, true)) then
      local splitTable = data:split("&$var")
      local start = 0
      local final = 0
      for it, str in ipairs(splitTable) do
        start, final = string.find(info, str, final + 1, true)
        if not(start) then
          pos = {}
          break
        else
          table.insert(pos, {["start"] = start, ["final"] = final})
          if (it == #splitTable) then return id, pos end
        end
      end
    elseif (info == data) then return id, pos end
  end
  return -1
end

local function getToken(info, pos, extract)
  local token = {}
  if (pos[1].start ~= 1) then
    table.insert(token, string.sub(info, 1, (pos[1].start) - 1))
    extract = extract - 1
  end
  for it, data in ipairs(pos) do
    if (extract == 0) then break
    elseif (data.final == #info) then break
    else
      if (it == #pos) then
        table.insert(token, string.sub(info, (data.final + 1), #info))
        extract = extract - 1
      else
        table.insert(token, string.sub(info, (data.final + 1), (pos[it + 1].start - 1)))
        extract = extract - 1
      end
    end
  end
  return token
end

function string:count(pattern)
  return select(2, string.gsub(self, pattern, ""))
end

function string:split(sep)
  local temp = {}
  local finder = 1
  local start, final = string.find(self, sep, finder, true)
  while start do
      if (start ~= 1) then
          table.insert(temp, string.sub(self, finder, start-1))
      end
      finder = final + 1
      start, final = string.find(self, sep, finder, true)
  end
  if (finder <= #self) then table.insert(temp, string.sub(self, finder)) end
  return temp
end

function loadEnhacedLangLib(idiom, lang)
  filename = "lang_" .. lang .. "_map.json"
  if (lib_lua.file_exists(filename)) then
    enhance["default"] = jsonFileGet(filename)
  else
    enhance["default"] = {}
  end
  filename = "lang_" .. lang .. "_" .. idiom .. ".json"
  if (lib_lua.file_exists(filename)) then
    enhance[idiom] = jsonFileGet(filename)
  else
    enhance[idiom] = enhance["default"]
  end
end

function enhanceMessage(info, idiom)
  local id, pos = getDefaultIdAndTokenPos(info)
  if (id == -1) then return "<case>" .. info
  else
    local str = ''
    if (#pos == 0) then return "<caseEnhanced>" .. enhance[idiom][id] .. "<caseOriginal>" .. info
    else
      local token = getToken(info, pos, enhance.default[id]:count("&$var"))
      local splitTable = {}
      if (enhance[idiom][id]) then splitTable = enhance[idiom][id]:split("&$var")
      else ehanced = info end
      local idPos = 1
      if (pos[idPos].start ~= 1) then
        str = str .. token[idPos]
        idPos = idPos + 1
      end
      for _, data in ipairs(splitTable) do
        str = str .. data
        if (idPos <= #token) then
          str = str .. token[idPos]
          idPos = idPos + 1
        end
      end
      if (idPos <= #token) then
        str = str .. token[idPos]
        idPos = idPos + 1
      end
      return "<caseEnhanced>" .. str .. "<caseOriginal>" .. info
    end
  end
end