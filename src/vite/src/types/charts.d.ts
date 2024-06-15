type TLeitnerChart = {
  boxes: TLeitnerBox[];
  urls: {
    restudy: string;
    new: string;
    due: string;
  };
};

type TLeitnerBox = [TLeitnerStack, TLeitnerStack];

type TLeitnerStack = {
  type: TLeitnerStackId;
  index: number;
  value: number;
};

type TLeitnerStackId = "failed" | "new" | "due" | "fresh" | "nill";
