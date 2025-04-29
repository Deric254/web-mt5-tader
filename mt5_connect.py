import sys
import MetaTrader5 as mt5
import pandas as pd
import json
from datetime import datetime, timedelta

def connect(server, login, password):
    if not mt5.initialize(server=server, login=int(login), password=password):
        print("Connection failed")
        return False
    print("Connection successful")
    return True

def get_symbol(server, login, password):
    if not mt5.initialize(server=server, login=int(login), password=password):
        return None
    symbols = mt5.symbols_get()
    for s in symbols:
        if 'DE30' in s.name.upper():
            if mt5.symbol_select(s.name, True):
                return s.name
    return None

def get_price(server, login, password):
    if not mt5.initialize(server=server, login=int(login), password=password):
        print("Connection failed")
        return False
    symbol = get_symbol(server, login, password)
    if not symbol:
        print("Failed to select symbol DE30")
        return False
    tick = mt5.symbol_info_tick(symbol)
    if tick is None:
        print("Failed to fetch price")
        return False
    print(tick.bid)
    return True

def get_bars(server, login, password):
    if not mt5.initialize(server=server, login=int(login), password=password):
        print("Connection failed")
        return False
    symbol = get_symbol(server, login, password)
    if not symbol:
        print("Failed to select symbol DE30")
        return False
    timeframe = mt5.TIMEFRAME_M15
    # Try live data first, fall back to historical if market closed
    bars = mt5.copy_rates_from_pos(symbol, timeframe, 0, 50)
    if bars is None or len(bars) == 0:
        print("No live data, fetching historical")
        bars = mt5.copy_rates_from(symbol, timeframe, datetime.now() - timedelta(days=10), 50)
    if bars is None:
        print("Failed to fetch bars")
        return False
    df = pd.DataFrame(bars)
    df['time'] = pd.to_datetime(df['time'], unit='s').dt.strftime('%Y-%m-%dT%H:%M:%S')
    df['donchian_high'] = df['high'].rolling(window=20).max()
    df['donchian_low'] = df['low'].rolling(window=20).min()
    result = df[['time', 'open', 'high', 'low', 'close', 'donchian_high', 'donchian_low']].to_dict('records')
    print(json.dumps(result))
    return True

def send_order(server, login, password, action, price, position_size):
    if not mt5.initialize(server=server, login=int(login), password=password):
        print("Connection failed")
        return False
    symbol = get_symbol(server, login, password)
    if not symbol:
        print("Failed to select symbol DE30")
        return False
    if action == "buy":
        request = {
            "action": mt5.TRADE_ACTION_DEAL,
            "symbol": symbol,
            "volume": float(position_size),
            "type": mt5.ORDER_TYPE_BUY,
            "price": float(price),
            "type_time": mt5.ORDER_TIME_GTC,
            "type_filling": mt5.ORDER_FILLING_IOC
        }
    elif action == "sell":
        request = {
            "action": mt5.TRADE_ACTION_DEAL,
            "symbol": symbol,
            "volume": float(position_size),
            "type": mt5.ORDER_TYPE_SELL,
            "price": float(price),
            "type_time": mt5.ORDER_TIME_GTC,
            "type_filling": mt5.ORDER_FILLING_IOC
        }
    result = mt5.order_send(request)
    if result.retcode == mt5.TRADE_RETCODE_DONE:
        print("Order successful")
        return True
    print(f"Order failed: {result.comment}")
    return False

def get_position(server, login, password):
    if not mt5.initialize(server=server, login=int(login), password=password):
        print("Connection failed")
        return False
    symbol = get_symbol(server, login, password)
    if not symbol:
        print("Failed to select symbol DE30")
        return False
    positions = mt5.positions_get(symbol=symbol)
    if positions:
        pos = positions[0]
        position = {
            "signal_type": "Long" if pos.type == mt5.ORDER_TYPE_BUY else "Short",
            "price": float(pos.price_open),
            "timestamp": pd.to_datetime(pos.time, unit='s').strftime('%Y-%m-%d %H:%M:%S'),
            "position_size": float(pos.volume)
        }
        print(json.dumps(position))
        return True
    print("No positions")
    return False

if __name__ == "__main__":
    command = sys.argv[1]
    server = sys.argv[2]
    login = sys.argv[3]
    password = sys.argv[4]
    if command == "connect":
        connect(server, login, password)
    elif command == "price":
        get_price(server, login, password)
    elif command == "bars":
        get_bars(server, login, password)
    elif command == "order":
        action = sys.argv[5]
        price = sys.argv[6]
        position_size = sys.argv[7]
        send_order(server, login, password, action, price, position_size)
    elif command == "position":
        get_position(server, login, password)