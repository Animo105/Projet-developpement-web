<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Node;
use App\Models\Card;

class NodeSeeder extends Seeder
{
    public function run(): void
    {
        $cards = Card::all();

        $node1 = Node::create(['x' => 0, 'y' => 0, 'parent_id' => null, 'locked_by_id' => null, 'element_id' => 11]);
        $node2 = Node::create(['x' => 1, 'y' => 0, 'parent_id' => 1, 'locked_by_id' => null, 'element_id' => 11]);
        $node3 = Node::create(['x' => 2, 'y' => 0, 'parent_id' => 2, 'locked_by_id' => null, 'element_id' => 11]);
        $node4 = Node::create(['x' => 3, 'y' => 0, 'parent_id' => 3, 'locked_by_id' => null, 'element_id' => 11]);
        $node5 = Node::create(['x' => 0, 'y' => 1, 'parent_id' => 1, 'locked_by_id' => null, 'element_id' => 11]);
        $node6 = Node::create(['x' => 1, 'y' => 1, 'parent_id' => 5, 'locked_by_id' => null, 'element_id' => 11]);
        $node7 = Node::create(['x' => 2, 'y' => 1, 'parent_id' => 6, 'locked_by_id' => null, 'element_id' => 11]);
        $node8 = Node::create(['x' => 0, 'y' => -1, 'parent_id' => 1, 'locked_by_id' => null, 'element_id' => 11]);
        $node9 = Node::create(['x' => 1, 'y' => -1, 'parent_id' => 8, 'locked_by_id' => null, 'element_id' => 11]);
        $node10 = Node::create(['x' => 2, 'y' => -1, 'parent_id' => 9, 'locked_by_id' => null, 'element_id' => 11]);

        $node11 = Node::create(['x' => 0, 'y' => 0, 'parent_id' => null, 'locked_by_id' => null, 'element_id' => 10]);
        $node12 = Node::create(['x' => 0, 'y' => 1, 'parent_id' => 11, 'locked_by_id' => null, 'element_id' => 10]);
        $node13 = Node::create(['x' => 1, 'y' => 0, 'parent_id' => 11, 'locked_by_id' => null, 'element_id' => 10]);
        $node14 = Node::create(['x' => 1, 'y' => 1, 'parent_id' => 12, 'locked_by_id' => null, 'element_id' => 10]);
        $node15 = Node::create(['x' => 1, 'y' => -1, 'parent_id' => 13, 'locked_by_id' => null, 'element_id' => 10]);
        $node16 = Node::create(['x' => 2, 'y' => 0, 'parent_id' => 13, 'locked_by_id' => null, 'element_id' => 10]);
        $node17 = Node::create(['x' => 2, 'y' => 1, 'parent_id' => 14, 'locked_by_id' => null, 'element_id' => 10]);
        $node18 = Node::create(['x' => 2, 'y' => -1, 'parent_id' => 16, 'locked_by_id' => 15, 'element_id' => 10]);
        $node19 = Node::create(['x' => 3, 'y' => 0, 'parent_id' => 16, 'locked_by_id' => null, 'element_id' => 10]);
        $node20 = Node::create(['x' => 3, 'y' => -1, 'parent_id' => 19, 'locked_by_id' => 18, 'element_id' => 10]);

        $node21 = Node::create(['x' => 0, 'y' => 0, 'parent_id' => null, 'locked_by_id' => null, 'element_id' => 8]);
        $node22 = Node::create(['x' => 0, 'y' => 0, 'parent_id' => null, 'locked_by_id' => null, 'element_id' => 9]);
        $node23 = Node::create(['x' => 0, 'y' => 0, 'parent_id' => null, 'locked_by_id' => null, 'element_id' => 12]);
        $node24 = Node::create(['x' => 0, 'y' => 0, 'parent_id' => null, 'locked_by_id' => null, 'element_id' => null]);

        $cards[0]->update(['node_id' => $node1->id]);
        $cards[1]->update(['node_id' => $node1->id]);
        $cards[2]->update(['node_id' => $node1->id]);
        $cards[3]->update(['node_id' => $node1->id]);
        $cards[4]->update(['node_id' => $node1->id]);
        $cards[5]->update(['node_id' => $node1->id]);
        $cards[6]->update(['node_id' => $node1->id]);
        $cards[13]->update(['node_id' => $node2->id]);
        $cards[20]->update(['node_id' => $node3->id]);
        $cards[27]->update(['node_id' => $node4->id]);
        $cards[7]->update(['node_id' => $node5->id]);
        $cards[14]->update(['node_id' => $node6->id]);
        $cards[21]->update(['node_id' => $node7->id]);
        $cards[8]->update(['node_id' => $node8->id]);
        $cards[9]->update(['node_id' => $node8->id]);
        $cards[10]->update(['node_id' => $node8->id]);
        $cards[11]->update(['node_id' => $node8->id]);
        $cards[12]->update(['node_id' => $node8->id]);
        $cards[15]->update(['node_id' => $node9->id]);
        $cards[16]->update(['node_id' => $node9->id]);
        $cards[17]->update(['node_id' => $node9->id]);
        $cards[18]->update(['node_id' => $node9->id]);
        $cards[19]->update(['node_id' => $node9->id]);
        $cards[22]->update(['node_id' => $node10->id]);
        $cards[23]->update(['node_id' => $node10->id]);
        $cards[24]->update(['node_id' => $node10->id]);
        $cards[25]->update(['node_id' => $node10->id]);
        $cards[26]->update(['node_id' => $node10->id]);

        $cards[28]->update(['node_id' => $node11->id]);
        $cards[29]->update(['node_id' => $node11->id]);
        $cards[30]->update(['node_id' => $node11->id]);
        $cards[31]->update(['node_id' => $node11->id]);
        $cards[32]->update(['node_id' => $node11->id]);
        $cards[33]->update(['node_id' => $node11->id]);
        $cards[34]->update(['node_id' => $node12->id]);
        $cards[40]->update(['node_id' => $node13->id]);
        $cards[41]->update(['node_id' => $node14->id]);
        $cards[35]->update(['node_id' => $node15->id]);
        $cards[36]->update(['node_id' => $node15->id]);
        $cards[37]->update(['node_id' => $node15->id]);
        $cards[38]->update(['node_id' => $node15->id]);
        $cards[39]->update(['node_id' => $node15->id]);
        $cards[47]->update(['node_id' => $node16->id]);
        $cards[48]->update(['node_id' => $node17->id]);
        $cards[42]->update(['node_id' => $node18->id]);
        $cards[43]->update(['node_id' => $node18->id]);
        $cards[44]->update(['node_id' => $node18->id]);
        $cards[45]->update(['node_id' => $node18->id]);
        $cards[46]->update(['node_id' => $node18->id]);
        $cards[54]->update(['node_id' => $node19->id]);
        $cards[49]->update(['node_id' => $node20->id]);
        $cards[50]->update(['node_id' => $node20->id]);
        $cards[51]->update(['node_id' => $node20->id]);
        $cards[52]->update(['node_id' => $node20->id]);
        $cards[53]->update(['node_id' => $node20->id]);
    }
}
